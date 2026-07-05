<?php

/**
 * ContactController
 *
 * GET  /kontakt → contact page with CSRF token
 * POST /kontakt → validate, sanitize, send contact form email
 *
 * Extends FormController — inherits shared security stack:
 *   CSRF, rate limiting, sanitization, email validation, DNS check.
 */

require_once __DIR__ . '/FormController.php';
require_once __DIR__ . '/../Models/PagesModel.php';
require_once __DIR__ . '/../Models/UrlModel.php';

class ContactController extends FormController
{
    /**
     * GET /kontakt
     */
    public function index(array $params = []): void
    {
        $pagesModel = new PagesModel();
        $urlModel   = new UrlModel();

        $sections = $pagesModel->getForPage('kontakt');
        $urls     = $urlModel->getForEntity('organisation', $this->org->id);

        $seo = $this->buildSeo($this->org, 'Kontakt | ' . $this->org->name);

        $this->render('pages/contact', [
            'sections'     => $sections,
            'urls'         => $urls,
            'pageKey'      => 'kontakt',
            'csrf_contact' => $this->ensureCsrf('csrf_contact'),
            'seo'          => $seo,
        ]);
    }

    /**
     * POST /kontakt
     */
    public function send(array $params = []): void
    {
        $this->startSession();

        // 1. CSRF
        if (!$this->validateCsrf('csrf_contact', 'csrf_contact')) {
            $this->jsonError('Ungültige Anfrage.');
            return;
        }

        // 2. Rate limit
        if (!$this->checkRateLimit('contact')) {
            $this->jsonError('Zu viele Nachrichten. Bitte warte einige Minuten.');
            return;
        }

        // 3. Sanitize
        $name    = $this->sanitizeField('name');
        $email   = $this->sanitizeEmail('email');
        $message = $this->sanitizeField('message');

        // 4. Required
        if (!$name || !$email || !$message) {
            $this->jsonError('Bitte alle Felder ausfüllen.');
            return;
        }

        // 5. Length limits
        if (strlen($name) < 2 || strlen($name) > 200) {
            $this->jsonError('Name muss zwischen 2 und 200 Zeichen lang sein.');
            return;
        }

        if (strlen($message) < 10 || strlen($message) > 5000) {
            $this->jsonError('Nachricht muss zwischen 10 und 5000 Zeichen lang sein.');
            return;
        }

        // 6. Email validation + DNS
        $emailError = $this->validateEmail($email);
        if ($emailError) {
            $this->jsonError($emailError);
            return;
        }

        // 7. Record attempt + regenerate CSRF
        $this->incrementRateLimit('contact');
        $this->regenerateCsrf('csrf_contact');

        $to      = $this->org->email ?? '';
        $orgName = $this->org->name  ?? 'KLA';

        if (!$to) {
            $this->jsonError('Empfängeradresse nicht konfiguriert.');
            return;
        }

        $body = Mailer::renderView('emails/contact-notification', [
            'name'    => $name,
            'email'   => $email,
            'message' => $message,
            'orgName' => $orgName,
        ]);

        $success = Mailer::send(
            to: $to,
            subject: 'Nachricht von ' . $name . ' über die Website',
            body: $body,
            fromName: $orgName,
            replyTo: $email
        );

        $success
            ? $this->jsonSuccess()
            : $this->jsonError('Fehler beim Senden. Bitte versuche es später erneut.');
    }
}
