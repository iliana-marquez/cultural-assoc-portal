<?php

/**
 * ContributorController
 *
 * GET  /unterstuetzer              → public listing + editor CRUD
 * POST /contributors/add           → add new draft contributor
 * POST /contributors/{id}/publish  → publish contributor
 * POST /contributors/{id}/unpublish → unpublish contributor
 * POST /contributors/{id}/save     → update single field
 * POST /contributors/{id}/delete   → soft delete (draft only)
 * POST /contributors/reorder       → update order_index
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/ContributorModel.php';
require_once __DIR__ . '/../Models/PagesModel.php';
require_once __DIR__ . '/../Models/MediaModel.php';
require_once __DIR__ . '/../Models/UrlModel.php';


class ContributorController extends BaseController
{
    private ContributorModel $contributorModel;
    private PagesModel $pagesModel;
    private MediaModel $mediaModel;

    public function __construct()
    {
        parent::__construct();
        $this->contributorModel = new ContributorModel();
        $this->pagesModel       = new PagesModel();
        $this->mediaModel       = new MediaModel();
    }

    /**
     * GET /unterstuetzer
     */
    public function index(array $params = []): void
    {
        $isLoggedIn   = $this->isLoggedIn();
        $contributors = $this->contributorModel->getAll(publishedOnly: !$isLoggedIn);
        $sections     = $this->pagesModel->getForPage('unterstuetzer');

        // Load profile image for each contributor
        foreach ($contributors as $contributor) {
            $images = $this->mediaModel->getForEntity('contributor', $contributor->id, 'profile');
            $contributor->profileImg = $images[0] ?? null;
        }

        // Load urls for each contributor
        $urlModel = new UrlModel();
        foreach ($contributors as $contributor) {
            $contributor->urls = $urlModel->getForEntity('contributor', $contributor->id);
        }

        $seo = $this->buildSeo($this->org, 'Unterstützer | ' . $this->org->name);

        $this->render('pages/unterstuetzer', [
            'contributors' => $contributors,
            'sections'     => $sections,
            'pageKey'      => 'unterstuetzer',
            'seo'          => $seo,
        ]);
    }

    /**
     * POST /contributors/add
     */
    public function add(array $params = []): void
    {
        $this->requireLogin();

        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            $this->jsonError('Name ist erforderlich.');
            return;
        }

        $id = $this->contributorModel->add($name);
        $id ? $this->jsonSuccess(['id' => $id]) : $this->jsonError('Fehler beim Erstellen.');
    }

    public function profileFragment(array $params = []): void
    {
        $this->requireLogin();
        $id = (int) ($params['id'] ?? 0);

        $contributor = $this->contributorModel->getById($id);
        if (!$contributor) {
            http_response_code(404);
            return;
        }

        $images = $this->mediaModel->getForEntity('contributor', $id, 'profile');
        $contributor->profileImg = $images[0] ?? null;

        $entity     = (object) [
            'id'          => $contributor->id,
            'displayName' => $contributor->name,
            'slug'        => null,
        ];
        $profileImg = $contributor->profileImg;
        $entityType = 'contributor';
        $isLoggedIn = true;

        require __DIR__ . '/../Views/components/profile-img.php';
    }

    /**
     * POST /contributors/{id}/save
     */
    public function save(array $params = []): void
    {
        $this->requireLogin();
        $id = (int) ($params['id'] ?? 0);

        $allowed = ['name', 'type', 'description', 'url'];
        $field   = null;
        $value   = null;

        foreach ($allowed as $f) {
            if (isset($_POST[$f])) {
                $field = $f;
                $value = trim($_POST[$f]);
                break;
            }
        }

        if (!$field) {
            $this->jsonError('Kein gültiges Feld.');
            return;
        }

        $success = $this->contributorModel->updateField($id, $field, $value);
        $success ? $this->jsonSuccess() : $this->jsonError('Fehler beim Speichern.');
    }

    /**
     * POST /contributors/{id}/publish
     */
    public function publish(array $params = []): void
    {
        $this->requireLogin();
        $id = (int) ($params['id'] ?? 0);

        $success = $this->contributorModel->publish($id);
        $success ? $this->jsonSuccess() : $this->jsonError('Fehler beim Veröffentlichen.');
    }

    /**
     * POST /contributors/{id}/unpublish
     */
    public function unpublish(array $params = []): void
    {
        $this->requireLogin();
        $id = (int) ($params['id'] ?? 0);

        $success = $this->contributorModel->unpublish($id);
        $success ? $this->jsonSuccess() : $this->jsonError('Fehler beim Zurückziehen.');
    }

    /**
     * POST /contributors/{id}/delete
     */
    public function delete(array $params = []): void
    {
        $this->requireLogin();
        $id = (int) ($params['id'] ?? 0);

        $success = $this->contributorModel->delete($id);
        $success ? $this->jsonSuccess() : $this->jsonError('Nur Entwürfe können gelöscht werden.');
    }
}
