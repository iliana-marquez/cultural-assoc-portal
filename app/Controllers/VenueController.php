<?php

/**
 * VenueController
 *
 * GET  /venues/search           → search venues for modal
 * POST /venues/add              → create new venue
 * POST /venues/{id}/save        → update venue field
 * POST /venues/{id}/delete      → hard delete (if not linked)
 * POST /venues/{id}/attach      → link venue to event
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/VenueModel.php';
require_once __DIR__ . '/../Models/UrlModel.php';

class VenueController extends BaseController
{
    private VenueModel $venueModel;
    private UrlModel   $urlModel;

    public function __construct()
    {
        parent::__construct();
        $this->venueModel = new VenueModel();
        $this->urlModel   = new UrlModel();
    }

    /**
     * GET /venues/search
     * Returns matching venues as JSON for modal search panel.
     */
    public function search(array $params = []): void
    {
        $this->requireLogin();
        $query  = trim($_GET['q'] ?? '');
        $venues = $query
            ? $this->venueModel->search($query)
            : $this->venueModel->getAll();

        $results = array_map(function ($v) {
            return [
                'id'          => $v->id,
                'name'        => $v->name,
                'address'     => VenueModel::formatAddress($v),
                'map_url'     => $v->map_url     ?? null,
                'website_url' => $v->website_url ?? null,
            ];
        }, $venues);

        $this->jsonSuccess(['venues' => $results]);
    }

    /**
     * POST /venues/add
     * Create a new venue and return its ID and display data.
     */
    public function add(array $params = []): void
    {
        $this->requireLogin();

        $name = trim($_POST['name'] ?? '');
        if (!$name) {
            $this->jsonError('Name is required');
            return;
        }

        $id = $this->venueModel->add([
            'name'        => $name,
            'street'      => trim($_POST['street']      ?? '') ?: null,
            'postcode'    => trim($_POST['postcode']    ?? '') ?: null,
            'city'        => trim($_POST['city']        ?? '') ?: null,
            'country'     => trim($_POST['country']     ?? '') ?: null,
            'map_url'     => trim($_POST['map_url']     ?? '') ?: null,
            'website_url' => trim($_POST['website_url'] ?? '') ?: null,
        ]);

        $venue = $this->venueModel->getById($id);

        $this->jsonSuccess([
            'id'          => $venue->id,
            'name'        => $venue->name,
            'address'     => VenueModel::formatAddress($venue),
            'map_url'     => $venue->map_url     ?? null,
            'website_url' => $venue->website_url ?? null,
        ]);
    }

    /**
     * POST /venues/{id}/save
     * Update a single venue field.
     */
    public function save(array $params = []): void
    {
        $this->requireLogin();
        $id      = (int) ($params['id'] ?? 0);
        $allowed = ['name', 'street', 'postcode', 'city', 'country', 'map_url', 'website_url'];

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
            $this->jsonError('No valid field');
            return;
        }

        $venue   = (array) $this->venueModel->getById($id);
        $success = $this->venueModel->update($id, array_merge($venue, [$field => $value ?: null]));

        $success ? $this->jsonSuccess() : $this->jsonError('Failed to save');
    }

    /**
     * POST /venues/{id}/delete
     * Hard delete — blocked if venue is linked to any events.
     */
    public function delete(array $params = []): void
    {
        $this->requireLogin();
        $id = (int) ($params['id'] ?? 0);

        if ($this->venueModel->isLinked($id)) {
            $this->jsonError('Venue is linked to existing events and cannot be deleted');
            return;
        }

        $success = $this->venueModel->delete($id);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to delete venue');
    }
}
