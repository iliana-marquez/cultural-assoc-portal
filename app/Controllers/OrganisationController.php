<?php

/**
 * OrganisationController
 *
 * GET  /{admin_path}/org       → edit() — org info edit page (login required)
 * POST /{admin_path}/org/save  → save() — update org info fields
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/OrganisationModel.php';
require_once __DIR__ . '/../Models/UrlModel.php';
require_once __DIR__ . '/../../core/CloudinaryService.php';

class OrganisationController extends BaseController
{
    private OrganisationModel $orgModel;
    private UrlModel          $urlModel;

    public function __construct()
    {
        parent::__construct();
        $this->orgModel = new OrganisationModel();
        $this->urlModel = new UrlModel();
    }

    /**
     * GET /{admin_path}/org
     * Org info edit page — editors only.
     */
    public function edit(array $params = []): void
    {
        $this->requireLogin();

        $urls = $this->urlModel->getForEntity('organisation', $this->org->id);

        $seo = $this->buildSeo(
            $this->org,
            'Organisation — ' . $this->org->name
        );

        $this->render('pages/org-edit', [
            'org'  => $this->org,
            'urls' => $urls,
            'seo'  => $seo,
        ]);
    }

    /**
     * POST /{admin_path}/org/save
     * Update a single org field via AJAX entity-edit-row.
     */
    public function save(array $params = []): void
    {
        $this->requireLogin();

        $allowed = [
            'name',
            'tagline',
            'description',
            'seo_description',
            'organisation_type',
            'email',
            'phone',
            'street',
            'postcode',
            'city',
            'country',
            'registration_number',
            'statutes_url',
            'schema_type',
        ];

        // Find which field is being saved
        $field = null;
        $value = null;
        foreach ($allowed as $f) {
            if (isset($_POST[$f])) {
                $field = $f;
                $value = trim($_POST[$f]);
                break;
            }
        }

        if (!$field) {
            $this->jsonError('No valid field provided');
            return;
        }

        $success = $this->orgModel->updateField($field, $value);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to save');
    }

    /**
     * POST /{admin_path}/org/logo/upload
     * Upload logo_url or inline_logo_url — deletes existing Cloudinary
     * file first (via extractPublicId on the stored URL) so re-uploads
     * never leave orphaned files behind.
     *
     * POST params:
     *   field  string  'logo_url' | 'inline_logo_url'
     */
    public function uploadLogo(array $params = []): void
    {
        $this->requireLogin();

        if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonError('Keine Datei hochgeladen.');
            return;
        }

        $field = trim($_POST['field'] ?? '');
        if (!in_array($field, ['logo_url', 'inline_logo_url'])) {
            $this->jsonError('Ungültiges Feld.');
            return;
        }

        // Delete existing logo from Cloudinary before uploading new one
        $existingUrl = $this->org->{$field} ?? null;
        if ($existingUrl) {
            $publicId = CloudinaryService::extractPublicId($existingUrl);
            if ($publicId) {
                CloudinaryService::delete($publicId);
            }
        }

        try {
            $suffix   = $field === 'logo_url' ? 'logo' : 'inline-logo';
            $publicId = CloudinaryService::generatePublicId('organisation-' . $suffix . '-' . time());
            $result   = CloudinaryService::upload($_FILES['image'], 'organisation', $publicId);

            $success = $this->orgModel->updateField($field, $result['secure_url']);

            if (!$success) {
                $this->jsonError('Speichern fehlgeschlagen.');
                return;
            }

            $this->jsonSuccess(['url' => $result['secure_url']]);
        } catch (RuntimeException $e) {
            $this->jsonError($e->getMessage());
        }
    }

    /**
     * POST /{admin_path}/org/logo/delete
     * Remove logo_url or inline_logo_url — deletes from Cloudinary
     * and clears the field.
     *
     * POST params:
     *   field  string  'logo_url' | 'inline_logo_url'
     */
    public function deleteLogo(array $params = []): void
    {
        $this->requireLogin();

        $field = trim($_POST['field'] ?? '');
        if (!in_array($field, ['logo_url', 'inline_logo_url'])) {
            $this->jsonError('Ungültiges Feld.');
            return;
        }

        $existingUrl = $this->org->{$field} ?? null;

        if ($existingUrl) {
            $publicId = CloudinaryService::extractPublicId($existingUrl);
            if ($publicId) {
                CloudinaryService::delete($publicId);
            }
        }

        $success = $this->orgModel->updateField($field, '');
        $success ? $this->jsonSuccess() : $this->jsonError('Fehler beim Löschen.');
    }
}
