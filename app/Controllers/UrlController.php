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
 * GET  /urls/named-pages      → real, navigable site pages, for the
 *                                picker's "Seite hier" tab
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
     * GET /urls/named-pages
     * Every real, navigable page on this site — the source of truth
     * for the picker modal's "Seite hier" tab. Reads directly from
     * the already-running Router instance via Router::getInstance() —
     * no constructor changes anywhere, no second list to maintain
     * alongside routes.php.
     */
    public function namedPages(array $params = []): void
    {
        $this->requireLogin();

        $router = Router::getInstance();
        $pages  = $router ? $router->getLinkablePages() : [];

        $this->jsonSuccess(['pages' => $pages]);
    }

    /**
     * GET /urls/fragment
     * Returns the rendered url-list HTML for a given entity — used
     * by the JS add/edit/remove success handlers to replace the
     * entire list's contents with a fresh, server-rendered render,
     * instead of patching the DOM incrementally. This guarantees
     * the list can never drift from the database, and correctly
     * handles the empty-state transition in both directions (list
     * becoming empty, or becoming non-empty for the first time)
     * without any special-case JS logic for either transition.
     *
     * Mirrors EventController::promoFragment()'s exact approach.
     *
     * GET params:
     *   entity_type  string
     *   entity_id    int
     */
    public function fragment(array $params = []): void
    {
        $this->requireLogin();

        $entityType = trim($_GET['entity_type'] ?? '');
        $entityId   = (int) ($_GET['entity_id'] ?? 0);

        if (!$entityType || !$entityId) {
            http_response_code(400);
            echo '';
            return;
        }

        $urls         = $this->urlModel->getForEntity($entityType, $entityId);
        $isLoggedIn   = $this->isLoggedIn();
        $fragmentOnly = true;

        ob_start();
        include __DIR__ . '/../Views/components/entity-urls.php';
        $html = ob_get_clean();

        header('Content-Type: text/html; charset=utf-8');
        echo $html;
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
    /**
     * POST /urls/add-internal-page
     * Attach a link to one of this site's own real pages (e.g. a
     * free section's CTA pointing at "Mitglied werden"). Distinct
     * from add() specifically so the server can verify the
     * submitted url genuinely belongs to this deployment — every
     * request to THIS route is, by definition, claiming to be an
     * internal page link, so that check can run unconditionally,
     * rather than as a client-reported flag on the generic add()
     * endpoint (which would let any caller claim "trust me, this is
     * internal" for an arbitrary url). Always uses the Website type,
     * since there's nothing for the editor to choose — every
     * internal page link is the same kind of destination.
     *
     * POST params:
     *   entity_type   string
     *   entity_id     int
     *   url           string  the full, absolute url (already built
     *                         client-side from the selected page path)
     *   label         string  optional
     *   cta_label     string  optional
     */
    public function addInternalPage(array $params = []): void
    {
        $this->requireLogin();

        $entityType = trim($_POST['entity_type'] ?? '');
        $entityId   = (int) ($_POST['entity_id'] ?? 0);
        $url        = trim($_POST['url'] ?? '');
        $label      = trim($_POST['label'] ?? '');
        $ctaLabel   = trim($_POST['cta_label'] ?? '');

        if (!$entityType || !$entityId || !$url) {
            $this->jsonError('entity_type, entity_id and url are required');
            return;
        }

        if (!UrlModel::isOwnDomain($url, $this->config['site_domain'])) {
            $this->jsonError('Diese URL gehört nicht zu dieser Website.');
            return;
        }

        $websiteType = null;
        foreach ($this->urlModel->getTypes() as $t) {
            if (strtolower($t->label) === 'website') {
                $websiteType = $t;
                break;
            }
        }
        if (!$websiteType) {
            $this->jsonError('Website-Typ nicht gefunden.');
            return;
        }

        $urlId = $this->urlModel->addForEntity(
            $entityType,
            $entityId,
            (int) $websiteType->id,
            $url,
            $label ?: null,
            $ctaLabel ?: null
        );

        if ($urlId === false) {
            $this->jsonError('Failed to add URL');
            return;
        }

        $saved = $this->urlModel->getById($urlId);

        $this->jsonSuccess([
            'id'          => $urlId,
            'url'         => $saved->url ?? null,
            'label'       => $saved->label ?? null,
            'url_type_id' => $websiteType->id,
            'type_label'  => $websiteType->label,
            'icon'        => $websiteType->icon ?? null,
            'cta_label'   => $ctaLabel ?: null,
        ]);
    }

    public function add(array $params = []): void
    {
        $this->requireLogin();

        $entityType = trim($_POST['entity_type'] ?? '');
        $entityId   = (int) ($_POST['entity_id'] ?? 0);
        $urlTypeId  = (int) ($_POST['url_type_id'] ?? 0);
        $url        = trim($_POST['url'] ?? '');
        $label      = trim($_POST['label'] ?? '');
        $ctaLabel   = trim($_POST['cta_label'] ?? '');

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
            $label ?: null,
            $ctaLabel ?: null
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
            'icon'        => $type->icon ?? null,
            'cta_label'   => $ctaLabel ?: null,
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
        $ctaLabel   = trim($_POST['cta_label'] ?? '');

        if (!$urlId || !$entityType || !$entityId) {
            $this->jsonError('url_id, entity_type and entity_id are required');
            return;
        }

        $success = $this->urlModel->attachToEntity($urlId, $entityType, $entityId, $ctaLabel ?: null);
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

        $urlId      = (int) ($params['id'] ?? 0);
        $urlTypeId  = (int) ($_POST['url_type_id'] ?? 0);
        $url        = trim($_POST['url'] ?? '');
        $label      = trim($_POST['label'] ?? '');
        $entityType = trim($_POST['entity_type'] ?? '');
        $entityId   = (int) ($_POST['entity_id'] ?? 0);
        $ctaLabel   = trim($_POST['cta_label'] ?? '');

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

        // Editing a CTA also needs to update THIS attachment's own
        // button text, which lives on the pivot, not on the url
        // itself — only attempted when both entity fields are given,
        // since a non-CTA save() call (the regular Links picker)
        // never sends these at all.
        if ($entityType && $entityId) {
            $this->urlModel->updateCtaLabel($urlId, $entityType, $entityId, $ctaLabel);
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
            'cta_label'   => $ctaLabel ?: null,
        ]);
    }
}
