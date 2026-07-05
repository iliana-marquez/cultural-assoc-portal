<?php

/**
 * MembershipRequestController
 *
 * GET  /mitglied-werden → handled by PageController (free page + membership-request-form component)
 * POST /mitglied-werden → validate, save to members table, send emails
 *
 * Extends FormController — inherits shared security stack:
 *   CSRF, rate limiting, sanitization, email validation, DNS check.
 */

require_once __DIR__ . '/FormController.php';
require_once __DIR__ . '/../Models/MemberModel.php';

class MembershipRequestController extends FormController
{
    private MemberModel $memberModel;

    public function __construct()
    {
        parent::__construct();
        $this->memberModel = new MemberModel();
    }

    /**
     * POST /mitglied-werden
     */
    public function send(array $params = []): void
    {
        $this->startSession();

        // 1. CSRF
        if (!$this->validateCsrf('csrf_membership', 'csrf_membership')) {
            $this->jsonError('Ungültige Anfrage.');
            return;
        }

        $this->startSession();
        error_log('SESSION csrf_membership: ' . ($_SESSION['csrf_membership'] ?? 'NOT SET'));
        error_log('POST csrf_membership: ' . ($_POST['csrf_membership'] ?? 'NOT SET'));

        // 2. Rate limit — 3 per 10 minutes per session
        if (!$this->checkRateLimit('membership_request')) {
            $this->jsonError('Zu viele Versuche. Bitte warte einige Minuten.');
            return;
        }

        // 3. Sanitize
        $firstName = $this->sanitizeField('first_name');
        $lastName  = $this->sanitizeField('last_name');
        $email     = mb_strtolower($this->sanitizeEmail('email'));

        // Capitalise Names
        $firstName = mb_strtoupper(mb_substr($firstName, 0, 1)) . mb_strtolower(mb_substr($firstName, 1));
        $lastName  = mb_strtoupper(mb_substr($lastName, 0, 1))  . mb_strtolower(mb_substr($lastName, 1));
        $street    = $this->sanitizeField('adresse');
        $plz       = $this->sanitizeField('plz');
        $city      = $this->sanitizeField('ort');
        $phone     = $this->sanitizeField('telefon');
        $birthDate = $this->sanitizeField('geburtstag');
        $newsletter = filter_input(INPUT_POST, 'newsletter', FILTER_UNSAFE_RAW) ? 1 : 0;

        // 4. Required fields
        if (!$firstName || !$lastName || !$email) {
            $this->jsonError('Bitte Vorname, Nachname und E-Mail ausfüllen.');
            return;
        }

        // 5. Length limits
        if (strlen($firstName) < 2 || strlen($firstName) > 100) {
            $this->jsonError('Vorname muss zwischen 2 und 100 Zeichen lang sein.');
            return;
        }

        if (strlen($lastName) < 2 || strlen($lastName) > 100) {
            $this->jsonError('Nachname muss zwischen 2 und 100 Zeichen lang sein.');
            return;
        }

        // 6. Email validation + DNS
        $emailError = $this->validateEmail($email);
        if ($emailError) {
            $this->jsonError($emailError);
            return;
        }

        if ($this->memberModel->emailExists($email)) {
            $this->jsonError('Diese E-Mail-Adresse ist bereits registriert.');
            return;
        }

        // 7. Generate payment reference — max 140 chars per SEPA free text rules
        $year             = date('Y');
        $paymentPurpose   = $this->org->payment_purpose ?? 'Mitgliedschaft';
        $paymentReference = mb_substr(
            "{$paymentPurpose}_{$firstName}-{$lastName}_{$year}",
            0,
            140
        );

        // 8. Save to members table
        $memberId = $this->memberModel->create([
            'first_name'        => mb_strtoupper(mb_substr($firstName, 0, 1)) . mb_strtolower(mb_substr($firstName, 1)),
            'last_name'         => mb_strtoupper(mb_substr($lastName, 0, 1))  . mb_strtolower(mb_substr($lastName, 1)),
            'email'             => $email,
            'street'            => $street ?: null,
            'plz'               => $plz    ?: null,
            'city'              => $city   ?: null,
            'phone'             => $phone  ?: null,
            'birth_date'        => $birthDate ?: null,
            'newsletter'        => $newsletter,
            'payment_reference' => $paymentReference,
        ]);

        if (!$memberId) {
            $this->jsonError('Fehler beim Speichern. Bitte versuche es später erneut.');
            return;
        }

        // 9. Record rate limit attempt + regenerate CSRF
        $this->incrementRateLimit('membership_request');
        $this->regenerateCsrf('csrf_membership');

        $to      = $this->org->email ?? '';
        $orgName = $this->org->name  ?? '';

        if (!$to) {
            $this->jsonError('Empfängeradresse nicht konfiguriert.');
            return;
        }

        // 10. Notification email to org
        $notificationBody = Mailer::renderView('emails/membership-request-notification', [
            'org'              => $this->org,
            'orgName'          => $orgName,
            'first_name'       => $firstName,
            'last_name'        => $lastName,
            'email'            => $email,
            'street'           => $street,
            'plz'              => $plz,
            'city'             => $city,
            'phone'            => $phone,
            'birth_date'       => $birthDate,
            'newsletter'       => $newsletter,
            'payment_reference' => $paymentReference,
        ]);

        Mailer::send(
            to: $to,
            subject: "Neue Mitgliedschaftsanfrage — {$firstName} {$lastName}",
            body: $notificationBody,
            fromName: $orgName,
            replyTo: $email
        );

        // 11. Confirmation email to applicant
        $confirmationBody = Mailer::renderView('emails/membership-request-confirmation', [
            'org'               => $this->org,
            'orgName'           => $orgName,
            'first_name'        => $firstName,
            'account_holder'    => $this->org->account_holder  ?? null,
            'iban'              => $this->org->iban             ?? null,
            'bic'               => $this->org->bic              ?? null,
            'payment_reference' => $paymentReference,
        ]);

        Mailer::send(
            to: $email,
            subject: "Danke, dass Sie Mitglied bei {$orgName} werden - so geht es weiter.",
            body: $confirmationBody,
            fromName: $orgName
        );

        $this->jsonSuccess();
    }
}
