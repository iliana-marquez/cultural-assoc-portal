<?php

/**
 * TeamController
 *
 * GET /team               → team listing
 * GET /team/{slug}        → team member detail
 * GET /team/{id}/profile-fragment → re-renders profile-img partial
 * POST /team/add          → create team member
 * POST /team/{id}/save    → update single field
 * POST /team/{id}/delete  → soft delete
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/TeamModel.php';
require_once __DIR__ . '/../Models/MediaModel.php';
require_once __DIR__ . '/../Models/PagesModel.php';
require_once __DIR__ . '/../Models/UrlModel.php';

class TeamController extends BaseController
{
    private TeamModel  $teamModel;
    private MediaModel $mediaModel;
    private PagesModel $pagesModel;
    private UrlModel   $urlModel;

    public function __construct()
    {
        parent::__construct();
        $this->teamModel  = new TeamModel();
        $this->mediaModel = new MediaModel();
        $this->pagesModel = new PagesModel();
        $this->urlModel   = new UrlModel();
    }

    // ── GET ──────────────────────────────────────────────────

    public function index(array $params = []): void
    {
        $sections = $this->pagesModel->getForPage('team');
        $members  = $this->teamModel->getAll();

        foreach ($members as $member) {
            $member->slug       = TeamModel::generateSlug($member->first_name, $member->last_name);
            $member->profileImg = $this->mediaModel->getFirstForEntity('team', $member->id, 'profile');
        }

        $seo = $this->buildSeo(
            $this->org,
            $this->org->name . ' | Team',
            'Das Team von ' . $this->org->name
        );

        $this->render('pages/team', [
            'sections' => $sections,
            'members'  => $members,
            'seo'      => $seo,
            'pageKey'  => 'team',
        ]);
    }

    public function show(array $params = []): void
    {
        $slug   = $params['slug'] ?? '';
        $member = $this->teamModel->getBySlug($slug);

        if (!$member) {
            $this->renderNotFound();
            return;
        }

        // Non-editors cannot view draft profiles
        if (!$this->isLoggedIn() && ($member->status ?? 'draft') === 'draft') {
            $this->renderNotFound();
            return;
        }

        $member->slug       = $slug;
        $member->displayName = TeamModel::displayName($member);
        $member->profileImg = $this->mediaModel->getFirstForEntity('team', $member->id, 'profile');
        $member->urls       = $this->urlModel->getForEntity('team', $member->id);

        $seo = $this->buildSeo(
            $this->org,
            $member->displayName . ' | ' . $this->org->name,
            $member->biography
                ? substr(strip_tags($member->biography), 0, 160)
                : ($member->motto ?? ''),
            $member->profileImg?->media_url ?? $this->org->logo_url ?? ''
        );

        $this->render('pages/team-detail', [
            'member'  => $member,
            'seo'     => $seo,
        ]);
    }

    // ── POST — CRUD ──────────────────────────────────────────

    public function add(array $params = []): void
    {
        $this->requireLogin();

        $id = $this->teamModel->add([
            'first_name' => 'Neues Teammitglied',
            'last_name'  => '',
            'role'       => '',
        ]);

        if (!$id) {
            $this->jsonError('Failed to create team member');
            return;
        }

        $member = $this->teamModel->getById($id);
        $slug   = TeamModel::generateSlug($member->first_name, $member->last_name);

        $this->jsonSuccess(['slug' => $slug]);
    }

    public function save(array $params = []): void
    {
        $this->requireLogin();
        $id = (int) ($params['id'] ?? 0);

        $allowed = ['title', 'first_name', 'last_name', 'role', 'profession', 'motto', 'biography'];

        $field = null;
        $value = null;
        foreach ($allowed as $f) {
            if (isset($_POST[$f])) {
                $field = $f;
                $value = trim($_POST[$f]) ?: null;
                break;
            }
        }

        if (!$field) {
            $this->jsonError('No valid field');
            return;
        }

        $success = $this->teamModel->updateField($id, $field, $value);

        if (!$success) {
            $this->jsonError('Failed to save');
            return;
        }

        if (in_array($field, ['first_name', 'last_name', 'title'], true)) {
            $member = $this->teamModel->getById($id);
            $slug   = TeamModel::generateSlug($member->first_name, $member->last_name);
            $this->jsonSuccess(['slug' => $slug]);
            return;
        }

        $this->jsonSuccess();
    }

    public function delete(array $params = []): void
    {
        $this->requireLogin();
        $id     = (int) ($params['id'] ?? 0);
        $member = $this->teamModel->getById($id);

        if (!$member) {
            $this->jsonError('Team member not found');
            return;
        }

        if (($member->status ?? 'draft') !== 'draft') {
            $this->jsonError('Only draft profiles can be deleted');
            return;
        }

        $success = $this->teamModel->delete($id);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to delete team member');
    }

    /**
     * POST /team/{id}/publish
     */
    public function publish(array $params = []): void
    {
        $this->requireLogin();
        $id      = (int) ($params['id'] ?? 0);
        $success = $this->teamModel->publish($id);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to publish team member');
    }

    /**
     * POST /team/{id}/unpublish
     */
    public function unpublish(array $params = []): void
    {
        $this->requireLogin();
        $id      = (int) ($params['id'] ?? 0);
        $success = $this->teamModel->unpublish($id);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to unpublish team member');
    }

    /**
     * POST /team/reorder
     * Bulk update order_index for drag-and-drop reordering.
     * Position 0 (legal representative) is never touched here —
     * only changed via OrganisationController::setLegalRepresentative()
     * from org-edit, never through team-grid dragging.
     *
     * POST params:
     *   order  JSON array  [{"id": 5, "order_index": 1}, ...]
     */
    public function reorder(array $params = []): void
    {
        $this->requireLogin();

        $order = json_decode($_POST['order'] ?? '[]', true);

        if (empty($order)) {
            $this->jsonError('No order data provided');
            return;
        }

        $this->teamModel->reorderTeam($order);

        $this->jsonSuccess();
    }

    /**
     * GET /team/{id}/profile-fragment
     * Re-renders the profile image partial for in-place refresh after upload/delete.
     */
    public function profileFragment(array $params = []): void
    {
        $id     = (int) ($params['id'] ?? 0);
        $member = $this->teamModel->getById($id);

        if (!$member) {
            http_response_code(404);
            echo '';
            return;
        }

        $member->displayName = TeamModel::displayName($member);
        $member->slug        = TeamModel::generateSlug($member->first_name, $member->last_name);
        $entity              = $member;
        $entityType          = 'team';
        $profileImg          = $this->mediaModel->getFirstForEntity('team', $id, 'profile');
        $isLoggedIn          = $this->isLoggedIn();

        ob_start();
        include __DIR__ . '/../Views/components/profile-img.php';
        $html = ob_get_clean();

        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }
}
