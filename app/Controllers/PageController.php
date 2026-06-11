<?php

/**
 * PageController
 *
 * Handles all free-section pages (GET) and section CRUD (POST).
 * Replaces HomeController — page_key derived from request URI.
 *
 * GET routes — all free pages:
 *   /                    → home
 *   /ueber-uns           → about
 *   /alsergrund          → district portrait
 *   /partner             → partners
 *   /sponsoren           → sponsors
 *   /mitglied-werden     → membership
 *   /archiv              → archive
 *
 * POST routes — section CRUD (edit mode, requireLogin):
 *   /page/section/add          → add new section to page
 *   /page/section/{id}/save    → update section content/layout
 *   /page/section/{id}/delete  → delete section
 *   /page/section/reorder      → update section order
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/PagesModel.php';

class PageController extends BaseController
{
    private PagesModel $pagesModel;

    // Page titles for SEO — headless default, overridden by DB content
    private array $pageTitles = [
        'home'            => '',
        'ueber-uns'       => 'Über uns',
        'alsergrund'      => 'Alsergrund',
        'partner'         => 'Partner',
        'sponsoren'       => 'Sponsoren',
        'mitglied-werden' => 'Mitglied werden',
        'archiv'          => 'Archiv',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->pagesModel = new PagesModel();
    }

    // ── GET — display page ───────────────────────────────────

    /**
     * Show any free-section page.
     * page_key derived from request URI — no hardcoding.
     */
    public function show(array $params = []): void
    {
        $pageKey  = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: 'home';
        $sections = $this->pagesModel->getForPage($pageKey);

        // SEO — $this->org loaded once in BaseController::__construct()
        $title = !empty($this->pageTitles[$pageKey])
            ? $this->org->name . ' | ' . $this->pageTitles[$pageKey]
            : $this->org->name;

        $seo = $this->buildSeo(
            $this->org,
            $title,
            $this->org->description ?? $this->org->tagline ?? '',
            $this->org->logo_url ?? '',
            'website',
            SchemaBuilder::build('organisation', $this->org)
        );

        $this->render('pages/free-page', [
            'sections' => $sections,
            'seo'      => $seo,
            'pageKey'  => $pageKey,
        ]);
    }

    // ── POST — section CRUD (edit mode) ─────────────────────

    /**
     * Add a new section to a page.
     * POST /page/section/add
     */
    public function addSection(array $params = []): void
    {
        $this->requireLogin();

        $pageKey    = trim($_POST['page_key'] ?? '');
        $sectionKey = trim($_POST['section_key'] ?? uniqid('section_'));
        $orderIndex = (int) ($_POST['order_index'] ?? 1);
        $content    = $_POST['content'] ?? '{}';

        if (empty($pageKey)) {
            $this->jsonError('page_key is required');
            return;
        }

        $success = $this->pagesModel->addSection(
            $pageKey,
            $sectionKey,
            $orderIndex,
            json_decode($content, true) ?? []
        );

        $success ? $this->jsonSuccess() : $this->jsonError('Failed to add section');
    }

    /**
     * Save section content and layout changes.
     * POST /page/section/{id}/save
     */
    public function saveSection(array $params = []): void
    {
        $this->requireLogin();

        $id      = (int) ($params['id'] ?? 0);
        $content = json_decode($_POST['content'] ?? '{}', true);

        if (!$id || !$content) {
            $this->jsonError('Invalid section data');
            return;
        }

        $success = $this->pagesModel->updateContent($id, $content);

        $success ? $this->jsonSuccess() : $this->jsonError('Failed to save section');
    }

    /**
     * Delete a section.
     * POST /page/section/{id}/delete
     */
    public function deleteSection(array $params = []): void
    {
        $this->requireLogin();

        $id = (int) ($params['id'] ?? 0);

        if (!$id) {
            $this->jsonError('Invalid section ID');
            return;
        }

        $success = $this->pagesModel->deleteSection($id);

        $success ? $this->jsonSuccess() : $this->jsonError('Failed to delete section');
    }

    /**
     * Reorder sections on a page.
     * POST /page/section/reorder
     * Expects: { "order": [{"id": 1, "order_index": 1}, ...] }
     */
    public function reorderSections(array $params = []): void
    {
        $this->requireLogin();

        $order = json_decode($_POST['order'] ?? '[]', true);

        if (empty($order)) {
            $this->jsonError('No order data provided');
            return;
        }

        foreach ($order as $item) {
            $this->pagesModel->updateOrder(
                (int) $item['id'],
                (int) $item['order_index']
            );
        }

        $this->jsonSuccess();
    }

    // ── Helpers ──────────────────────────────────────────────

    /**
     * JSON success response for AJAX POST requests.
     */
    private function jsonSuccess(array $data = []): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true] + $data);
        exit;
    }

    /**
     * JSON error response for AJAX POST requests.
     */
    private function jsonError(string $message): void
    {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }
}
