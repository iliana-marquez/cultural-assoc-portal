<?php

/**
 * BaseController
 * 
 * Foundation class extended by all controllers.
 * Provides view rendering, redirects and session helpers.
 */

class BaseController
{
    /**
     * Render a view inside a main layout.
     * 
     * @param string $view  Path to view file relative to app/Views/
     *                      e.g. 'pages/home' renders app/Views/pages/home.php
     * @param array $data   Data to extract and make available in the view
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
     * Redirect to given URL
     * 
     * @param string $url URL to redirect
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Check if the current user is logged in (edit mode active)
     * 
     * @return bool
     */
    protected function isLoggedIn(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['user_id']);
    }

    /**
     * Require login - redirect to loginpage if not authenticated.
     * Used in admin controllers to protect every action.
     */
    protected function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/wkk');
        }
    }
}
