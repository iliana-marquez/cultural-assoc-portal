<?php

/**
 * NewsletterController
 *
 * POST /newsletter/subscribe           → add subscriber, send confirmation email
 * GET  /newsletter/confirm/{token}     → confirm subscription
 * GET  /newsletter/unsubscribe/{token} → one-click unsubscribe
 * GET  /newsletter/export              → CSV export (editor only)
 * GET  /newsletter/subscribers         → subscriber list view (editor only)
 *
 * Security:
 *   - strip_tags + filter_var on email
 *   - RateLimiter — 3 per 10 min per session
 *   - CSRF token on subscribe form
 *   - Cryptographically secure token via bin2hex(random_bytes(32))
 *   - Token expiry — 24 hours
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/NewsletterModel.php';
require_once __DIR__ . '/../../core/Mailer.php';
require_once __DIR__ . '/../../core/RateLimiter.php';

class NewsletterController extends BaseController
{
    private NewsletterModel $newsletterModel;

    public function __construct()
    {
        parent::__construct();
        $this->newsletterModel = new NewsletterModel();
    }

    /**
     * POST /newsletter/subscribe
     */
    public function subscribe(array $params = []): void
    {
        $this->startSession();

        // CSRF
        $token = filter_input(INPUT_POST, 'csrf_newsletter', FILTER_SANITIZE_SPECIAL_CHARS);
        if (!$token || !hash_equals($_SESSION['csrf_newsletter'] ?? '', $token)) {
            $this->jsonError('Ungültige Anfrage.');
            return;
        }

        // Rate limit
        if (!RateLimiter::check('newsletter', 3, 600)) {
            $this->jsonError('Zu viele Versuche. Bitte warte einige Minuten.');
            return;
        }

        $email = strip_tags(trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? ''));

        if (!$email) {
            $this->jsonError('Bitte E-Mail-Adresse eingeben.');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonError('Bitte eine gültige E-Mail-Adresse eingeben.');
            return;
        }

        if (strlen($email) > 200) {
            $this->jsonError('E-Mail-Adresse zu lang.');
            return;
        }

        $domain = substr(strrchr($email, '@'), 1);
        if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
            $this->jsonError('E-Mail-Domain konnte nicht verifiziert werden.');
            return;
        }

        RateLimiter::increment('newsletter');

        $existing = $this->newsletterModel->getByEmail($email);

        if ($existing) {
            if ($existing->confirmed) {
                // Already confirmed — silent success (don't reveal subscription status)
                $this->jsonSuccess(['message' => 'Bitte überprüfen Sie Ihre E-Mails zur Bestätigung.']);
                return;
            }
            // Unconfirmed — resend confirmation
            $confirmToken = bin2hex(random_bytes(32));
            $this->newsletterModel->updateToken((int) $existing->id, $confirmToken);
        } else {
            $confirmToken = bin2hex(random_bytes(32));
            $ok = $this->newsletterModel->add($email, $confirmToken);
            if (!$ok) {
                $this->jsonError('Anmeldung fehlgeschlagen. Bitte versuche es später erneut.');
                return;
            }
        }

        // Regenerate CSRF
        $_SESSION['csrf_newsletter'] = bin2hex(random_bytes(32));

        $orgName    = $this->org->name ?? 'KLA';
        $protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $confirmUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/newsletter/confirm/' . $confirmToken;

        $body = Mailer::renderView('emails/newsletter-confirm', [
            'orgName'    => $orgName,
            'confirmUrl' => $confirmUrl,
        ]);

        Mailer::send(
            to: $email,
            subject: 'Newsletter bestätigen — ' . $orgName,
            body: $body,
            fromName: $orgName
        );

        $this->jsonSuccess(['message' => 'Bitte überprüfen Sie Ihre E-Mails zur Bestätigung.']);
    }

    /**
     * GET /newsletter/confirm/{token}
     */
    public function confirm(array $params = []): void
    {
        $token      = $params['token'] ?? '';
        $subscriber = $this->newsletterModel->getByToken($token);

        if (!$subscriber) {
            $this->render('pages/newsletter-result', [
                'seo'     => $this->buildSeo($this->org, 'Newsletter'),
                'success' => false,
                'message' => 'Der Bestätigungslink ist ungültig oder abgelaufen.',
            ]);
            return;
        }

        $this->newsletterModel->confirm((int) $subscriber->id);

        $this->render('pages/newsletter-result', [
            'seo'     => $this->buildSeo($this->org, 'Newsletter'),
            'success' => true,
            'message' => 'Vielen Dank! Sie sind jetzt für den Newsletter angemeldet.',
        ]);
    }

    /**
     * GET /newsletter/unsubscribe/{token}
     */
    public function unsubscribe(array $params = []): void
    {
        $token   = $params['token'] ?? '';
        $success = $this->newsletterModel->deleteByToken($token);

        $this->render('pages/newsletter-result', [
            'seo'     => $this->buildSeo($this->org, 'Newsletter'),
            'success' => $success,
            'message' => $success
                ? 'Sie wurden erfolgreich vom Newsletter abgemeldet.'
                : 'Der Link ist ungültig oder bereits verwendet.',
        ]);
    }

    /**
     * GET /newsletter/subscribers
     * Editor only — subscriber list. Returns 404 for non-editors.
     */
    public function subscribers(array $params = []): void
    {
        if (!$this->isLoggedIn()) {
            $this->renderNotFound();
            return;
        }

        $subscribers = $this->newsletterModel->getAll();

        $this->render('pages/newsletter-subscribers', [
            'seo'         => $this->buildSeo($this->org, 'Newsletter Abonnenten'),
            'subscribers' => $subscribers,
        ]);
    }

    /**
     * GET /newsletter/export
     * Editor only — CSV download. Returns 404 for non-editors.
     */
    public function export(array $params = []): void
    {
        if (!$this->isLoggedIn()) {
            $this->renderNotFound();
            return;
        }
        $subscribers = $this->newsletterModel->getAllForExport();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="newsletter-subscribers-' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['E-Mail', 'Bestätigt am']);

        foreach ($subscribers as $s) {
            fputcsv($out, [$s->email, $s->confirmed_at]);
        }

        fclose($out);
        exit;
    }
}
