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
            $participant->promo       = $this->mediaModel->getPromo('participant', $participant->id);
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
        $participant->media       = $this->mediaModel->getForEntity('participant', $participant->id);

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
        $id      = (int) ($params['id'] ?? 0);
        $success = $this->participantModel->update($id, $_POST);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to save participant');
    }

    public function delete(array $params = []): void
    {
        $this->requireLogin();
        $id      = (int) ($params['id'] ?? 0);
        $success = $this->participantModel->delete($id);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to delete participant');
    }
}
