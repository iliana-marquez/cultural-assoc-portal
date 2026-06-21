<?php

/**
 * UrlController
 *
 * Manages external URLs via the shared urls/entity_urls pivot system.
 * All mutating routes require login — public visitors only ever see
 * URLs rendered server-side via UrlModel::getForEntity(), called
 * directly from whichever controller renders the entity's page
 * (e.g. EventController::show()) — there is no public GET route here
 * for listing an entity's URLs.
 *
 * GET  /urls/search           → search existing URLs (picker modal)
 * POST /urls/add              → create-or-reuse a URL, attach to an entity
 * POST /urls/{id}/attach      → attach an already-existing URL to an entity
 * POST /urls/{id}/unlink      → remove a URL from one entity (may delete
 *                                the URL record too, if now fully orphaned)
 * POST /urls/{id}/delete      → force-delete a URL entirely, regardless
 *                                of how many entities still reference it
 * POST /urls/{id}/save        → update a URL's string, type, and/or label
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/UrlModel.php';

class UrlController extends BaseController
{
    private UrlModel $urlModel;

    public function __construct()
    {
        parent::__construct();
        $this->urlModel = new UrlModel();
    }

    /**
     * GET /urls/types
     * List all available URL types (Website, Email, Instagram, etc.),
     * so the frontend can show real labels and never needs to guess
     * at or hardcode url_type_id values.
     */
    public function types(array $params = []): void
    {
        $this->requireLogin();

        $types = $this->urlModel->getTypes();
        $this->jsonSuccess(['types' => $types]);
    }

    /**
     * GET /urls/search
     * Search existing URLs by label or url string, for the picker
     * modal's "choose existing" tab.
     *
     * GET params:
     *   q  string  search query
     */
    public function search(array $params = []): void
    {
        $this->requireLogin();

        $query = trim($_GET['q'] ?? '');

        if ($query === '') {
            $this->jsonSuccess(['urls' => []]);
            return;
        }

        $results = $this->urlModel->search($query);
        $this->jsonSuccess(['urls' => $results]);
    }

    /**
     * POST /urls/add
     * Create a new URL (or reuse an existing one with the same
     * normalized url string) and attach it to an entity.
     *
     * POST params:
     *   entity_type   string
     *   entity_id     int
     *   url_type_id   int
     *   url           string
     *   label         string  optional
     */
    public function add(array $params = []): void
    {
        $this->requireLogin();

        $entityType = trim($_POST['entity_type'] ?? '');
        $entityId   = (int) ($_POST['entity_id'] ?? 0);
        $urlTypeId  = (int) ($_POST['url_type_id'] ?? 0);
        $url        = trim($_POST['url'] ?? '');
        $label      = trim($_POST['label'] ?? '');

        if (!$entityType || !$entityId || !$urlTypeId || !$url) {
            $this->jsonError('entity_type, entity_id, url_type_id and url are required');
            return;
        }

        $validationError = $this->urlModel->validateForType($url, $urlTypeId);
        if ($validationError !== null) {
            $this->jsonError($validationError);
            return;
        }

        $urlId = $this->urlModel->addForEntity(
            $entityType,
            $entityId,
            $urlTypeId,
            $url,
            $label ?: null
        );

        if ($urlId === false) {
            $this->jsonError('Failed to add URL');
            return;
        }

        $saved = $this->urlModel->getById($urlId);
        $types = $this->urlModel->getTypes();
        $type  = null;
        foreach ($types as $t) {
            if ((int) $t->id === $urlTypeId) {
                $type = $t;
                break;
            }
        }

        $this->jsonSuccess([
            'id'          => $urlId,
            'url'         => $saved->url ?? null,
            'label'       => $saved->label ?? null,
            'url_type_id' => $urlTypeId,
            'type_label'  => $type->label ?? null,
            'icon'       => $type->icon ?? null,
        ]);
    }

    /**
     * POST /urls/{id}/attach
     * Attach an already-existing URL (by id) to an entity, without
     * creating a new URL record. Used by the picker modal's
     * "choose existing" confirm step.
     *
     * POST params:
     *   entity_type  string
     *   entity_id    int
     */
    public function attach(array $params = []): void
    {
        $this->requireLogin();

        $urlId      = (int) ($params['id'] ?? 0);
        $entityType = trim($_POST['entity_type'] ?? '');
        $entityId   = (int) ($_POST['entity_id'] ?? 0);

        if (!$urlId || !$entityType || !$entityId) {
            $this->jsonError('url_id, entity_type and entity_id are required');
            return;
        }

        $success = $this->urlModel->attachToEntity($urlId, $entityType, $entityId);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to attach URL');
    }

    /**
     * POST /urls/{id}/unlink
     * Remove a URL from one entity.
     *
     * Two-step confirm flow: if this is the URL's only remaining
     * link, removing it would also delete the underlying record.
     * Rather than doing that silently, the first call (without
     * `confirmed=1`) returns { needsConfirmation: true } and performs
     * no mutation at all — the caller is expected to show the editor
     * a warning, then resend the same request with `confirmed=1` to
     * actually proceed. If this isn't the last link, it proceeds
     * immediately either way, since nothing destructive happens.
     *
     * POST params:
     *   entity_type  string
     *   entity_id    int
     *   confirmed    '1'     optional — skips the warning step
     */
    public function unlink(array $params = []): void
    {
        $this->requireLogin();

        $urlId      = (int) ($params['id'] ?? 0);
        $entityType = trim($_POST['entity_type'] ?? '');
        $entityId   = (int) ($_POST['entity_id'] ?? 0);
        $confirmed  = ($_POST['confirmed'] ?? '') === '1';

        if (!$urlId || !$entityType || !$entityId) {
            $this->jsonError('url_id, entity_type and entity_id are required');
            return;
        }

        $linkCount = $this->urlModel->countLinks($urlId);
        $isLastLink = $linkCount <= 1;

        if ($isLastLink && !$confirmed) {
            $this->jsonSuccess([
                'needsConfirmation' => true,
                'willDelete'        => true,
            ]);
            return;
        }

        $success = $this->urlModel->unlinkFromEntity($urlId, $entityType, $entityId);

        if (!$success) {
            $this->jsonError('Failed to remove URL');
            return;
        }

        $this->jsonSuccess(['deleted' => $isLastLink]);
    }

    /**
     * POST /urls/{id}/delete
     * Force-delete a URL entirely, regardless of how many entities
     * still reference it. Distinct from unlink — this is the rarer,
     * more deliberate action for a URL that's simply wrong everywhere.
     */
    public function delete(array $params = []): void
    {
        $this->requireLogin();

        $urlId = (int) ($params['id'] ?? 0);

        if (!$urlId) {
            $this->jsonError('url_id required');
            return;
        }

        $success = $this->urlModel->delete($urlId);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to delete URL');
    }

    /**
     * POST /urls/{id}/save
     * Update a URL's string, type, and/or label. Reflects immediately
     * on every entity currently linked to it.
     *
     * POST params:
     *   url_type_id  int
     *   url          string
     *   label        string  optional
     */
    public function save(array $params = []): void
    {
        $this->requireLogin();

        $urlId     = (int) ($params['id'] ?? 0);
        $urlTypeId = (int) ($_POST['url_type_id'] ?? 0);
        $url       = trim($_POST['url'] ?? '');
        $label     = trim($_POST['label'] ?? '');

        if (!$urlId || !$urlTypeId || !$url) {
            $this->jsonError('url_id, url_type_id and url are required');
            return;
        }

        $validationError = $this->urlModel->validateForType($url, $urlTypeId);
        if ($validationError !== null) {
            $this->jsonError($validationError);
            return;
        }

        $success = $this->urlModel->update($urlId, $url, $urlTypeId, $label ?: null);

        if (!$success) {
            $this->jsonError('Failed to update URL');
            return;
        }

        $saved = $this->urlModel->getById($urlId);
        $types = $this->urlModel->getTypes();
        $type  = null;
        foreach ($types as $t) {
            if ((int) $t->id === $urlTypeId) {
                $type = $t;
                break;
            }
        }

        $this->jsonSuccess([
            'id'          => $urlId,
            'url'         => $saved->url ?? null,
            'label'       => $saved->label ?? null,
            'url_type_id' => $urlTypeId,
            'type_label'  => $type->label ?? null,
            'icon'        => $type->icon ?? null,
        ]);
    }
}
