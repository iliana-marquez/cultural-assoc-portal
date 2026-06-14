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
}
