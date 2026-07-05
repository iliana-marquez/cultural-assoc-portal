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
require_once __DIR__ . '/../Models/UrlModel.php';
require_once __DIR__ . '/../Models/TeamModel.php';
require_once __DIR__ . '/../../core/CloudinaryService.php';

class PageController extends BaseController
{
    private PagesModel $pagesModel;
    private UrlModel   $urlModel;
    private TeamModel  $teamModel;

    // Page titles for SEO — headless default, overridden by DB content
    private array $pageTitles = [
        'home'            => '',
        'ueber-uns'       => 'Über uns',
        'alsergrund'      => 'Alsergrund',
        'partner'         => 'Partner',
        'sponsoren'       => 'Sponsoren',
        'mitglied-werden' => 'Mitglied werden',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->pagesModel = new PagesModel();
        $this->urlModel   = new UrlModel();
        $this->teamModel  = new TeamModel();
    }

    // ── GET — display page ───────────────────────────────────

    /**
     * GET /datenschutz
     */
    public function datenschutz(array $params = []): void
    {
        $sections  = $this->pagesModel->getForPage('datenschutz');
        $legalRep = $this->teamModel->getLegalRepresentative();

        $seo = $this->buildSeo(
            $this->org,
            'Datenschutzerklärung | ' . $this->org->name
        );

        $this->render('pages/datenschutz', [
            'sections'  => $sections,
            'legalRep' => $legalRep,
            'seo'       => $seo,
            'pageKey'   => 'datenschutz',
        ]);
    }

    /**
     * GET /impressum
     */
    public function impressum(array $params = []): void
    {
        $sections  = $this->pagesModel->getForPage('impressum');
        $legalRep = $this->teamModel->getLegalRepresentative();

        $seo = $this->buildSeo(
            $this->org,
            'Impressum | ' . $this->org->name
        );

        $this->render('pages/impressum', [
            'sections'  => $sections,
            'legalRep' => $legalRep,
            'seo'       => $seo,
            'pageKey'   => 'impressum',
        ]);
    }

    /**
     * Show any free-section page.
     * page_key derived from request URI — no hardcoding.
     */
    public function show(array $params = []): void
    {
        $pageKey  = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: 'home';
        $sections = $this->pagesModel->getForPage($pageKey);

        // For membership-request-form
        $csrfMembership = null;
        if ($pageKey === 'mitglied-werden') {
            $this->startSession();
            if (empty($_SESSION['csrf_membership'])) {
                $_SESSION['csrf_membership'] = bin2hex(random_bytes(32));
            }
            $csrfMembership = $_SESSION['csrf_membership'];
        }

        // SEO — $this->org loaded once in BaseController::__construct()
        $title = !empty($this->pageTitles[$pageKey])
            ? $this->org->name . ' | ' . $this->pageTitles[$pageKey]
            : $this->org->name;

        $seo = $this->buildSeo(
            $this->org,
            $title,
            '',
            '',
            'website',
            SchemaBuilder::build('organisation', $this->org)
        );

        $this->render('pages/free-page', [
            'sections' => $sections,
            'seo'      => $seo,
            'pageKey'  => $pageKey,
            'csrf_membership' => $csrfMembership,
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

        $id = $this->pagesModel->addSection(
            $pageKey,
            $sectionKey,
            $orderIndex,
            json_decode($content, true) ?? []
        );

        $id ? $this->jsonSuccess(['id' => $id]) : $this->jsonError('Failed to add section');
    }

    /**
     * Save section content and layout changes.
     * POST /page/section/{id}/save
     */
    public function saveSection(array $params = []): void
    {
        $this->requireLogin();

        $id = (int) ($params['id'] ?? 0);

        if (!$id) {
            $this->jsonError('Invalid section ID');
            return;
        }

        // Get existing section content from DB
        $existing = $this->pagesModel->getById($id);
        $current  = json_decode($existing->content ?? '{}', true) ?: [];

        // Merge POST fields into existing content
        $updatable = [
            'title',
            'subtitle',
            'text',
            'theme',
            'layout',
            'image_pos',
            'object_fit',
            'align',
            'image',
            'image_credit',
            'bg_image',
        ];

        // Allowed HTML tags for rich text fields — strip dangerous tags, keep formatting
        $richTextFields = ['title', 'subtitle', 'text'];
        $allowedTags    = '<span><a><br><div><p><strong><em><ul><li>';

        foreach ($updatable as $field) {
            if (isset($_POST[$field])) {
                $value = $_POST[$field];
                if (in_array($field, $richTextFields)) {
                    $value = strip_tags($value, $allowedTags);
                }
                $current[$field] = $value;
            }
        }

        $success = $this->pagesModel->updateContent($id, $current);

        $success ? $this->jsonSuccess() : $this->jsonError('Failed to save section');
    }

    /**
     * POST /page/section/{id}/remove-image
     * Remove image or bg_image from section JSON — also deletes from
     * Cloudinary since section images are stored as bare URLs, not
     * tracked via entity_media, so this is the only place that can
     * trigger their deletion.
     */
    public function removeSectionImage(array $params = []): void
    {
        $this->requireLogin();

        $id    = (int) ($params['id'] ?? 0);
        $field = trim($_POST['field'] ?? '');

        if (!$id || !in_array($field, ['image', 'bg_image'])) {
            $this->jsonError('Invalid request');
            return;
        }

        $existing = $this->pagesModel->getById($id);
        $current  = json_decode($existing->content ?? '{}', true) ?: [];

        // Delete from Cloudinary if a URL exists for this field
        if (!empty($current[$field])) {
            $publicId = CloudinaryService::extractPublicId($current[$field]);
            if ($publicId) {
                CloudinaryService::delete($publicId);
            }
        }

        $current[$field] = null;

        $success = $this->pagesModel->updateContent($id, $current);

        $success ? $this->jsonSuccess() : $this->jsonError('Failed to remove image');
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

        // Clean up this section's CTAs first — same orphan-aware
        // unlink used everywhere else, so each underlying url either
        // stays (if reused on another entity) or is genuinely
        // deleted (if this was its last reference), rather than
        // leaving permanently orphaned entity_urls/urls rows behind.
        $ctaUrls = $this->urlModel->getForEntity('section', $id);
        foreach ($ctaUrls as $cta) {
            $this->urlModel->unlinkFromEntity($cta->id, 'section', $id);
        }

        // Fetch BEFORE deleting, since the row won't exist afterward
        // and the gap-closing step needs to know exactly which page
        // and which order_index just became free.
        $section = $this->pagesModel->getById($id);

        // Clean up section images from Cloudinary — same reasoning as
        // removeSectionImage(): these URLs aren't tracked via entity_media,
        // so deleting the section row would otherwise leave them orphaned.
        if ($section) {
            $content = json_decode($section->content ?? '{}', true) ?: [];
            foreach (['image', 'bg_image'] as $field) {
                if (!empty($content[$field])) {
                    $publicId = CloudinaryService::extractPublicId($content[$field]);
                    if ($publicId) {
                        CloudinaryService::delete($publicId);
                    }
                }
            }
        }

        $success = $this->pagesModel->deleteSection($id);

        // Close the resulting gap so the remaining sequence stays
        // clean (e.g. 1,2,4 becomes 1,2,3) — only for ordinary
        // sections; the reserved order_index=0 slot has special
        // meaning (a hero, or the capped intro), so shifting other
        // sections to "fill" position 0 would be incorrect, not tidy.
        if ($success && $section && (int) $section->order_index >= 1) {
            $this->pagesModel->closeOrderGap($section->page_key, (int) $section->order_index);
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

    // jsonSuccess() and jsonError() inherited from BaseController
}
