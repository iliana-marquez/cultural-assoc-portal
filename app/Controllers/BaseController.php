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
    protected array  $config;
    protected object $org;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/app.php';

        // Organisation data — available in all controllers and views
        require_once __DIR__ . '/../Models/OrganisationModel.php';
        $this->org = (new OrganisationModel())->get() ?? (object)[];

        // Organisation's external links — available everywhere $org is
        // (nav, footer), since they're rendered on every page, not just
        // the editor's own org-edit page.
        require_once __DIR__ . '/../Models/UrlModel.php';
        $this->org->urls = isset($this->org->id)
            ? (new UrlModel())->getForEntity('organisation', $this->org->id)
            : [];
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
        // Always available in every view
        $data['isLoggedIn'] = $this->isLoggedIn();
        $data['config']     = $this->config;
        $data['org']        = $this->org;

        extract($data);

        ob_start();
        require __DIR__ . '/../Views/' . $view . '.php';
        $content = ob_get_clean();

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
    /**
     * JSON success response for AJAX requests.
     */
    protected function jsonSuccess(array $data = []): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true] + $data);
        exit;
    }

    /**
     * JSON error response for AJAX requests.
     */
    protected function jsonError(string $message): void
    {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }

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

    /**
     * Build SEO data array for any page.
     * Used by all controllers — no duplication.
     *
     * @param object $org         Organisation data from organisation_info
     * @param string $title       Page title — defaults to org name
     * @param string $description Meta description — defaults to org description
     * @param string $image       OG image URL — defaults to org logo
     * @param string $type        OG type — 'website' or 'article'
     * @param string $schema      JSON-LD schema string from SchemaBuilder
     * @return array              $seo array for main.php
     */
    protected function buildSeo(
        object $org,
        string $title = '',
        string $description = '',
        string $image = '',
        string $type = 'website',
        string $schema = ''
    ): array {
        return [
            'title'       => $title ?: $org->name,
            'description' => $description ?: ($org->description ?? $org->tagline ?? ''),
            'image'       => $image ?: ($org->logo_url ?? ''),
            'url'         => 'https://' . $_SERVER['HTTP_HOST']
                . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
            'type'        => $type,
            'schema'      => $schema,
        ];
    }

    /**
     * Render 404 page.
     */
    public function renderNotFound(): void
    {
        $this->render('pages/404');
    }
}
