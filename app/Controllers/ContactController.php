<?php

/**
 * ContactController
 *
 * GET  /kontakt → contact page with CSRF token
 * POST /kontakt → validate, sanitize, send contact form email
 *
 * Security layers:
 *   1. CSRF token — prevents cross-site request forgery
 *   2. filter_input() — safe POST access
 *   3. strip_tags() — removes HTML/script injection
 *   4. htmlspecialchars() — XSS output encoding
 *   5. filter_var(FILTER_VALIDATE_EMAIL) — email format
 *   6. checkdnsrr() — email domain existence
 *   7. Length limits — DoS prevention
 *   8. RateLimiter — abuse prevention
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/PagesModel.php';
require_once __DIR__ . '/../Models/UrlModel.php';
require_once __DIR__ . '/../../core/Mailer.php';
require_once __DIR__ . '/../../core/RateLimiter.php';

class ContactController extends BaseController
{
    /**
     * GET /kontakt
     * Generate CSRF token and pass to view.
     */
    public function index(array $params = []): void
    {
        $pagesModel = new PagesModel();
        $urlModel   = new UrlModel();

        $sections = $pagesModel->getForPage('kontakt');
        $urls     = $urlModel->getForEntity('organisation', 1);

        // Generate CSRF token for contact form
        $this->startSession();
        if (empty($_SESSION['csrf_contact'])) {
            $_SESSION['csrf_contact'] = bin2hex(random_bytes(32));
        }

        $this->render('pages/contact', [
            'sections'     => $sections,
            'urls'         => $urls,
            'pageKey'      => 'kontakt',
            'csrf_contact' => $_SESSION['csrf_contact'],
        ]);
    }

    /**
     * POST /kontakt
     */
    public function send(array $params = []): void
    {
        $this->startSession();

        // 1. CSRF validation
        $token = filter_input(INPUT_POST, 'csrf_contact', FILTER_SANITIZE_SPECIAL_CHARS);
        if (!$token || !hash_equals($_SESSION['csrf_contact'] ?? '', $token)) {
            $this->jsonError('Ungültige Anfrage.');
            return;
        }

        // 2. Rate limit — 3 per 10 minutes per session
        if (!RateLimiter::check('contact', 3, 600)) {
            $this->jsonError('Zu viele Nachrichten. Bitte warte einige Minuten.');
            return;
        }

        // 3. Safe input access via filter_input
        $name    = strip_tags(trim(filter_input(INPUT_POST, 'name',    FILTER_DEFAULT) ?? ''));
        $email   = strip_tags(trim(filter_input(INPUT_POST, 'email',   FILTER_SANITIZE_EMAIL) ?? ''));
        $message = strip_tags(trim(filter_input(INPUT_POST, 'message', FILTER_DEFAULT) ?? ''));

        // 4. Required fields
        if (!$name || !$email || !$message) {
            $this->jsonError('Bitte alle Felder ausfüllen.');
            return;
        }

        // 5. Length limits
        if (strlen($name) < 2 || strlen($name) > 200) {
            $this->jsonError('Name muss zwischen 2 und 200 Zeichen lang sein.');
            return;
        }

        if (strlen($email) > 200) {
            $this->jsonError('E-Mail-Adresse zu lang.');
            return;
        }

        if (strlen($message) < 10 || strlen($message) > 5000) {
            $this->jsonError('Nachricht muss zwischen 10 und 5000 Zeichen lang sein.');
            return;
        }

        // 6. Email format validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonError('Bitte eine gültige E-Mail-Adresse eingeben.');
            return;
        }

        // 7. Email domain DNS check
        $domain = substr(strrchr($email, '@'), 1);
        if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
            $this->jsonError('E-Mail-Domain konnte nicht verifiziert werden.');
            return;
        }

        // 8. Record attempt after all validation passes
        RateLimiter::increment('contact');

        // 9. Regenerate CSRF token after successful validation
        $_SESSION['csrf_contact'] = bin2hex(random_bytes(32));

        $to      = $this->org->email ?? '';
        $orgName = $this->org->name  ?? 'KLA';

        if (!$to) {
            $this->jsonError('Empfängeradresse nicht konfiguriert.');
            return;
        }

        $subject = 'Nachricht von ' . $name . ' über die Website';

        $body = Mailer::renderView('emails/contact-notification', [
            'name'    => $name,
            'email'   => $email,
            'message' => $message,
            'orgName' => $orgName,
        ]);

        $success = Mailer::send(
            to: $to,
            subject: $subject,
            body: $body,
            fromName: $orgName,
            replyTo: $email
        );

        if ($success) {
            $this->jsonSuccess(['message' => 'Nachricht gesendet. Wir melden uns bald!']);
        } else {
            $this->jsonError('Fehler beim Senden. Bitte versuche es später erneut.');
        }
    }
}
