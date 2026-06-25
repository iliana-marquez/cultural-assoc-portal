<?php

/**
 * ParticipantController
 *
 * GET /kuenstlerinnen          → participant listing
 * GET /kuenstlerinnen/{slug}   → participant detail
 * POST /participants/add       → add participant
 * POST /participants/{id}/save   → update participant
 * POST /participants/{id}/delete → delete participant
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/ParticipantModel.php';
require_once __DIR__ . '/../Models/EventModel.php';
require_once __DIR__ . '/../Models/MediaModel.php';
require_once __DIR__ . '/../Models/UrlModel.php';
require_once __DIR__ . '/../Models/PagesModel.php';
require_once __DIR__ . '/../Models/EventModel.php';

class ParticipantController extends BaseController
{
    private ParticipantModel $participantModel;
    private MediaModel       $mediaModel;
    private UrlModel         $urlModel;
    private PagesModel       $pagesModel;
    private EventModel       $eventModel;

    public function __construct()
    {
        parent::__construct();
        $this->participantModel = new ParticipantModel();
        $this->mediaModel       = new MediaModel();
        $this->urlModel         = new UrlModel();
        $this->pagesModel       = new PagesModel();
        $this->eventModel       = new EventModel();
    }

    // ── GET ──────────────────────────────────────────────────

    public function index(array $params = []): void
    {
        $sections     = $this->pagesModel->getForPage('kuenstlerinnen');
        $participants = $this->participantModel->getAll();

        foreach ($participants as $participant) {
            $participant->displayName = ParticipantModel::displayName($participant);
            $participant->slug        = ParticipantModel::generateSlug($participant);
            $participant->profileImg  = $this->mediaModel->getProfile('participant', $participant->id);
        }

        $seo = $this->buildSeo(
            $this->org,
            $this->org->name . ' | Künstler:innen',
            'Künstler:innen und Ensembles bei ' . $this->org->name
        );

        $this->render('pages/participants', [
            'sections'     => $sections,
            'participants' => $participants,
            'seo'          => $seo,
            'pageKey'      => 'kuenstlerinnen',
        ]);
    }

    public function show(array $params = []): void
    {
        $slug        = $params['slug'] ?? '';
        $participant = $this->participantModel->getBySlug($slug);

        if (!$participant) {
            $this->renderNotFound();
            return;
        }

        $participant->displayName = ParticipantModel::displayName($participant);
        $participant->slug        = $slug;
        $participant->urls        = $this->urlModel->getForEntity('participant', $participant->id);

        // Profile image — stored in entity_media (stage='profile'), URL also
        // synced to the direct image column for use in listings/cards.
        $profileMedia             = $this->mediaModel->getForEntity('participant', $participant->id, 'profile');
        $participant->profileImg  = $profileMedia[0] ?? null;

        // Events this participant appeared in
        $participant->events = $this->eventModel->getForParticipant($participant->id);

        foreach ($participant->events as $event) {
            $event->slug = EventModel::generateSlug($event->title);
        }

        $seo = $this->buildSeo(
            $this->org,
            $participant->displayName . ' | ' . $this->org->name,
            $participant->field ?? '',
            $participant->media[0]->media_url ?? $this->org->logo_url ?? ''
        );

        $this->render('pages/participant-detail', [
            'participant' => $participant,
            'seo'         => $seo,
        ]);
    }

    // ── POST — CRUD ──────────────────────────────────────────

    public function add(array $params = []): void
    {
        $this->requireLogin();
        $success = $this->participantModel->add($_POST);
        $success
            ? $this->jsonSuccess(['id' => $this->participantModel->lastInsertId()])
            : $this->jsonError('Failed to add participant');
    }

    public function save(array $params = []): void
    {
        $this->requireLogin();
        $id = (int) ($params['id'] ?? 0);

        $allowed = [
            'type',
            'title',
            'first_name',
            'last_name',
            'field',
            'bio',
        ];

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

        $success = $this->participantModel->updateField($id, $field, $value);

        if (!$success) {
            $this->jsonError('Failed to save');
            return;
        }

        // If a name field changed the slug changes too — return it
        if (in_array($field, ['title', 'first_name', 'last_name'], true)) {
            $participant = $this->participantModel->getById($id);
            $slug        = ParticipantModel::generateSlug($participant);
            $this->jsonSuccess(['slug' => $slug]);
            return;
        }

        $this->jsonSuccess();
    }

    public function delete(array $params = []): void
    {
        $this->requireLogin();
        $id      = (int) ($params['id'] ?? 0);
        $success = $this->participantModel->delete($id);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to delete participant');
    }

    /**
     * GET /participants/{id}/profile-fragment
     * Re-renders the profile image partial for in-place refresh after upload/delete.
     */
    public function profileFragment(array $params = []): void
    {
        $id          = (int) ($params['id'] ?? 0);
        $participant = $this->participantModel->getById($id);

        if (!$participant) {
            http_response_code(404);
            echo '';
            return;
        }

        $participant->displayName = ParticipantModel::displayName($participant);
        $participant->slug        = ParticipantModel::generateSlug($participant);
        $profileMedia             = $this->mediaModel->getForEntity('participant', $id, 'profile');
        $entity                   = $participant;
        $entityType               = 'participant';
        $profileImg               = $profileMedia[0] ?? null;
        $isLoggedIn               = $this->isLoggedIn();

        ob_start();
        include __DIR__ . '/../Views/components/profile-img.php';
        $html = ob_get_clean();

        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }
}
