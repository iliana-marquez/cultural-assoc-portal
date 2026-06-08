<?php

/**
 * AuthController
 *
 * Handles the full OTP authentication flow:
 *   GET  /{admin_path}          → show login form
 *   POST /{admin_path}          → validate email → generate OTP → send email
 *   GET  /{admin_path}/verify   → show verify form
 *   POST /{admin_path}/verify   → validate OTP → create session → redirect to /
 *   GET  /logout                → destroy session → redirect to /
 *
 * {admin_path} configured in config/app.php → admin_path 
 * 
 *  Uses PRG pattern (Post/Redirect/Get) — prevents form resubmission on refresh.
 *
 * Session scenarios:
 *   pending_editor_id present   → valid OTP sent → redirect to verify
 *   pending_editor_id absent    → no OTP, fresh login form
 *   rate limit exhausted        → clears all pending data → fresh login form
 *   successful login            → clears all pending data → edit mode active 
 * 
 * Uses:
 *   EditorModel       → extends UserModel — entity lookup + OTP flow
 *   OrganisationModel → org name for email branding
 *   Mailer            → email sending + template rendering
 *   RateLimiter       → max attempts per window — config: otp_max_attempts, otp_rate_limit_window
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/EditorModel.php';
require_once __DIR__ . '/../Models/OrganisationModel.php';
require_once __DIR__ . '/../../core/Mailer.php';
require_once __DIR__ . '/../../core/RateLimiter.php';

class AuthController extends BaseController
{
    private EditorModel       $editorModel;
    private OrganisationModel $orgModel;

    public function __construct()
    {
        parent::__construct(); // runs BaseController's constructor → loads $this->config
        $this->editorModel = new EditorModel();
        $this->orgModel    = new OrganisationModel();
    }

    /**
     * GET /{admin_path} 
     * Show the login form.
     * 
     * Redirects to / if already logged in.
     * Redirects to verify if valid pending OTP exists — preserves the attempt.
     * Shows fresh login form if no pending OTP.
     */
    public function showLogin(array $params = []): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/');
        }

        $this->startSession();

        // No pending OTP — fresh login form
        $this->render('admin/login', []);
    }

    /**
     * POST /{admin_path} 
     * Validate email → generate OTP → send email.
     * PRG pattern — redirects after POST to prevent resubmission on refresh.
     * 
     * Rate limit exhausted → clears all pending session data → fresh login form.
     * Invalid email format → no attempt counted.
     * Valid format → attempt counted → OTP generated if registered.
     * Always redirects to verify — registered or not, no info leaked.
     */
    public function sendOtp(array $params = []): void
    {
        $this->startSession();

        // Rate limiting — config: otp_max_attempts + otp_rate_limit_window
        if (!RateLimiter::check('login', $this->config['otp_max_attempts'], $this->config['otp_rate_limit_window'])) {
            // Clear all pending data — no session limbo after exhausted attempts
            unset($_SESSION['pending_email']);
            unset($_SESSION['pending_editor_id']);

            $this->render('admin/login', [
                'error' => 'Zu viele Versuche. Bitte probieren Sie es später erneut.'
            ]);
            return;
        }

        $email = strtolower(trim($_POST['email'] ?? ''));

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('admin/login', [
                'error' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.'
            ]);
            return;
        }

        // Only increment after format is valid
        RateLimiter::increment('login');

        // Check if email is authorised
        $editor = $this->editorModel->findByEmail($email);

        if ($editor) {
            $code    = $this->editorModel->generateOtp($editor->id, $this->config['otp_expiry']);
            $org     = $this->orgModel->get();
            $orgName = $org->name ?? 'Organisation Website System';

            // Render and send OTP email
            Mailer::send(
                $email,
                'Ihr Anmeldecode — ' . $orgName,
                Mailer::renderView('emails/otp', [
                    'code'      => $code,
                    'orgName'   => $orgName,
                    'userName'  => $editor->name,
                    'expiryMin' => $this->config['otp_expiry'] / 60,
                ]),
                $orgName
            );

            // Store editor id in session for verify step
            $_SESSION['pending_editor_id'] = $editor->id;
        }

        // PRG — store email in session, redirect to GET verify
        // Always redirect — registered or not, no info leaked
        $_SESSION['pending_email'] = $email;
        $this->redirect('/' . $this->config['admin_path'] . '/verify');
    }

    /**
     * GET /{admin_path}/verify
     * Show the verify form.
     * 
     * Requires pending_editor_id — proof a real OTP was generated.
     * No pending_editor_id → redirect to login (fresh start).
     */
    public function showVerify(array $params = []): void
    {
        $this->startSession();

        // Redirect only if editor hasn't gone through login form
        // pending_email = proof the form was submitted (registered or not)
        if (!isset($_SESSION['pending_email'])) {
            $this->redirect('/' . $this->config['admin_path']);
            return;
        }

        $this->render('admin/verify', [
            'email' => $_SESSION['pending_email'] ?? ''
        ]);
    }

    /**
     * POST /{admin_path} /verify
     * Validate OTP → create session → redirect to /.
     */
    public function verifyOtp(array $params = []): void
    {
        $this->startSession();

        $code     = trim($_POST['code'] ?? '');
        $editorId = $_SESSION['pending_editor_id'] ?? null;

        if (empty($code) || !$editorId) {
            $this->redirect('/' . $this->config['admin_path']);
            return;
        }

        if (!$this->editorModel->validateOtp((int) $editorId, $code)) {
            $this->render('admin/verify', [
                'email' => $_SESSION['pending_email'] ?? '',
                'error' => 'Ungültiger oder abgelaufener Code. Bitte versuchen Sie es erneut.'
            ]);
            return;
        }

        // Valid — fetch editor, clear OTP, create session
        $editor = $this->editorModel->findById((int) $editorId);
        $this->editorModel->clearOtp((int) $editorId);

        // Regenerate session ID — prevents session fixation attacks
        session_regenerate_id(true);

        // Reset rate limit + clear all pending data
        RateLimiter::reset('login');
        unset($_SESSION['pending_editor_id']);
        unset($_SESSION['pending_email']);

        $_SESSION['user_id']            = $editor->id;
        $_SESSION['user_name']          = $editor->name;
        $_SESSION['can_manage_editors'] = $editor->can_manage_editors;

        $this->redirect('/');
    }

    /**
     * GET /logout
     * Destroy session → redirect to /.
     */
    public function logout(array $params = []): void
    {
        $this->startSession();
        session_destroy();
        $this->redirect('/');
    }
}
