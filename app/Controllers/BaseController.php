<?php

/**
 * BaseController
 *
 * Foundation class extended by all controllers.
 * Provides view rendering, redirects, session helpers and config access.
 *
 * Loads config/app.php once in constructor — available as $this->config
 * in all extending controllers. No hardcoded paths or settings.
 */

class BaseController
{
    protected array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/app.php';
    }

    /**
     * Render a view inside the main layout.
     * Always uses layouts/main.php — one layout wraps all pages.
     * $content is available inside main.php as the page-specific output.
     *
     * @param string $view  Path to view relative to app/Views/ e.g. 'pages/home'
     * @param array  $data  Data to extract and make available in the view
     */
    protected function render(string $view, array $data = []): void
    {
        // Make data variables available in the view
        extract($data);

        // Capture the view content
        ob_start();
        require __DIR__ . '/../Views/' . $view . '.php';
        $content = ob_get_clean();

        // Load the main layout - $content is available inside it.
        require __DIR__ . '/../Views/layouts/main.php';
    }

    /**
     * Redirect to a given URL.
     *
     * @param string $url URL to redirect to
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Check if the current user is logged in (edit mode active).
     *
     * @return bool
     */
    protected function isLoggedIn(): bool
    {
        $this->startSession();
        return isset($_SESSION['user_id']);
    }

    /**
     * Require login — redirect to admin login if not authenticated.
     * Uses admin_path from config — no hardcoded paths.
     */
    protected function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/' . $this->config['admin_path']);
        }
    }

    /**
     * Start session if not already started.
     * Uses session_name from config.
     */
    protected function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name($this->config['session_name']);
            session_start();
        }
    }
}
