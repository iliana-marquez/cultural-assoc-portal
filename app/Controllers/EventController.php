<?php

/**
 * EventController
 *
 * GET /veranstaltungen          → event listing (upcoming + past)
 * GET /veranstaltungen/{slug}   → event detail
 * GET /archiv                   → archive listing (pre-2025)
 * POST /events/add              → add event
 * POST /events/{id}/save        → update event
 * POST /events/{id}/delete      → delete event
 * POST /events/{id}/participant/add    → add participant to event
 * POST /events/{id}/participant/remove → remove participant from event
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/EventModel.php';
require_once __DIR__ . '/../Models/ParticipantModel.php';
require_once __DIR__ . '/../Models/MediaModel.php';
require_once __DIR__ . '/../Models/PagesModel.php';
require_once __DIR__ . '/../Models/VenueModel.php';

class EventController extends BaseController
{
    private EventModel       $eventModel;
    private ParticipantModel $participantModel;
    private MediaModel       $mediaModel;
    private PagesModel       $pagesModel;
    private VenueModel       $venueModel;

    public function __construct()
    {
        parent::__construct();
        $this->eventModel       = new EventModel();
        $this->participantModel = new ParticipantModel();
        $this->mediaModel       = new MediaModel();
        $this->pagesModel       = new PagesModel();
        $this->venueModel       = new VenueModel();
    }

    // ── GET — display ────────────────────────────────────────

    /**
     * GET /veranstaltungen
     * Event listing — upcoming and past sections.
     */
    public function index(array $params = []): void
    {
        $sections  = $this->pagesModel->getForPage('veranstaltungen');
        $upcoming  = $this->eventModel->getUpcoming();
        $past      = $this->eventModel->getPast();
        $categories = $this->eventModel->getCategories();

        // Add slug + promo image to each event
        foreach (array_merge($upcoming, $past) as $event) {
            $event->slug  = EventModel::generateSlug($event->title);
            $event->promo = $this->mediaModel->getPromo('event', $event->id);
        }

        $seo = $this->buildSeo(
            $this->org,
            $this->org->name . ' | Veranstaltungen',
            'Kulturprogramm von ' . $this->org->name
        );

        $this->render('pages/events', [
            'sections'   => $sections,
            'upcoming'   => $upcoming,
            'past'       => $past,
            'categories' => $categories,
            'seo'        => $seo,
        ]);
    }

    /**
     * GET /veranstaltungen/{slug}
     * Event detail page.
     */
    public function show(array $params = []): void
    {
        $slug  = $params['slug'] ?? '';
        $event = $this->eventModel->getBySlug($slug);

        if (!$event) {
            $this->renderNotFound();
            return;
        }

        $event->slug         = $slug;
        $event->status       = EventModel::getStatus($event);
        $event->participants = $this->participantModel->getForEvent($event->id);
        $event->media        = $this->mediaModel->getForEntity('event', $event->id);

        // Add slug + displayName to each participant for linking
        foreach ($event->participants as $participant) {
            $participant->displayName = ParticipantModel::displayName($participant);
            $participant->slug        = ParticipantModel::generateSlug($participant);
        }

        $seo = $this->buildSeo(
            $this->org,
            $event->title . ' | ' . $this->org->name,
            $event->description
                ? substr(strip_tags($event->description), 0, 160)
                : '',
            $event->media[0]->media_url ?? $this->org->logo_url ?? '',
            'article',
            SchemaBuilder::build('occurrence', $event)
        );

        $isLoggedIn      = $this->isLoggedIn();
        $venues          = $isLoggedIn ? $this->venueModel->getAll() : [];
        $allParticipants = $isLoggedIn ? $this->participantModel->getAll() : [];
        $categories      = $isLoggedIn ? $this->eventModel->getCategories() : [];

        $this->render('pages/event-detail', [
            'event'           => $event,
            'venues'          => $venues,
            'allParticipants' => $allParticipants,
            'categories'      => $categories,
            'seo'             => $seo,
        ]);
    }

    /**
     * GET /archiv
     * Archive listing — events before 2025.
     */
    public function archive(array $params = []): void
    {
        $sections = $this->pagesModel->getForPage('archiv');
        $events   = $this->eventModel->getArchive();

        foreach ($events as $event) {
            $event->slug  = EventModel::generateSlug($event->title);
            $event->promo = $this->mediaModel->getPromo('event', $event->id);
        }

        $seo = $this->buildSeo(
            $this->org,
            $this->org->name . ' | Archiv',
            'Vergangene Veranstaltungen von ' . $this->org->name
        );

        $this->render('pages/archive', [
            'sections' => $sections,
            'events'   => $events,
            'seo'      => $seo,
        ]);
    }

    // ── POST — CRUD ──────────────────────────────────────────

    public function add(array $params = []): void
    {
        $this->requireLogin();

        // Create minimal event → redirect to detail for inline editing
        $title = $_POST['title'] ?? 'Neue Veranstaltung';
        $date  = $_POST['date']  ?? date('Y-m-d');

        $success = $this->eventModel->add([
            'title' => $title,
            'date'  => $date,
        ]);

        if (!$success) {
            $this->jsonError('Failed to create event');
            return;
        }

        $id   = $this->eventModel->lastInsertId();
        $event = $this->eventModel->getById($id);
        $slug = EventModel::generateSlug($event->title);

        $this->jsonSuccess(['slug' => $slug]);
    }

    /**
     * POST /events/{id}/save
     * Update a single field via entity-edit-row AJAX.
     */
    public function save(array $params = []): void
    {
        $this->requireLogin();
        $id = (int) ($params['id'] ?? 0);

        $allowed = [
            'title',
            'subtitle',
            'description',
            'date',
            'time',
            'venue_id',
            'category_id',
            'review',
            'admission',
            'admission_amount',
            'admission_url',
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

        $success = $this->eventModel->updateField($id, $field, $value);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to save');
    }

    public function delete(array $params = []): void
    {
        $this->requireLogin();
        $id      = (int) ($params['id'] ?? 0);
        $success = $this->eventModel->delete($id);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to delete event');
    }

    public function addParticipant(array $params = []): void
    {
        $this->requireLogin();
        $eventId       = (int) ($params['id'] ?? 0);
        $participantId = (int) ($_POST['participant_id'] ?? 0);
        $success       = $this->eventModel->addParticipant($eventId, $participantId);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to add participant');
    }

    public function removeParticipant(array $params = []): void
    {
        $this->requireLogin();
        $eventId       = (int) ($params['id'] ?? 0);
        $participantId = (int) ($_POST['participant_id'] ?? 0);
        $success       = $this->eventModel->removeParticipant($eventId, $participantId);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to remove participant');
    }
}
