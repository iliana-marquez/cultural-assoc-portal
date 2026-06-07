<?php

/**
 * AuthController
 *
 * Handles the full OTP authentication flow:
 *   GET  /{admin_path}         → show login form
 *   POST /{admin_path}         → validate email → generate OTP → send email
 *   POST /{admin_path}/verify   → validate OTP → create session → redirect to /
 *   GET  /logout               → destroy session → redirect to /
 *
 * {admin_path} configured in config/app.php → admin_path 
 *
 * Uses:
 *   EditorModel       → extends UserModel — entity lookup + OTP flow
 *   OrganisationModel → org name for email branding
 *   Mailer            → email sending + template rendering
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/EditorModel.php';
require_once __DIR__ . '/../Models/OrganisationModel.php';

class AuthController extends BaseController
{
    private EditorModel       $editorModel;
    private OrganisationModel $orgModel;

    public function __construct()
    {
        $this->editorModel = new EditorModel();
        $this->orgModel    = new OrganisationModel();
        $this->config      = require __DIR__ . '/../../config/app.php';
    }

    /**
     * GET /{admin_path} 
     * Show the login form.
     * Redirect to / if already logged in.
     */
    public function showLogin(array $params = []): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/');
        }

        $this->render('admin/login', []);
    }

    /**
     * POST /{admin_path} 
     * Validate email → generate OTP → send email.
     */
    public function sendOtp(array $params = []): void
    {
        $email = strtolower(trim($_POST['email'] ?? ''));

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('admin/login', [
                'error' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.'
            ]);
            return;
        }

        // Check if email is authorised
        // Silent fail — never reveal if email exists or not
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
            $this->startSession();
            $_SESSION['pending_editor_id'] = $editor->id;
        }

        // Always show verify form — don't reveal if email was found
        $this->render('admin/verify', [
            'email' => $email
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
                'error' => 'Ungültiger oder abgelaufener Code. Bitte versuchen Sie es erneut.'
            ]);
            return;
        }

        // Valid — fetch editor, clear OTP, create session
        $editor = $this->editorModel->findById((int) $editorId);
        $this->editorModel->clearOtp((int) $editorId);

        unset($_SESSION['pending_editor_id']);

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
