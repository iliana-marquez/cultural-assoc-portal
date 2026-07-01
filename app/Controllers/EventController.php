<?php

/**
 * EventController
 *
 * GET /veranstaltungen          → upcoming events only (all past → /archiv)
 * GET /veranstaltungen/{slug}   → event detail
 * GET /archiv                   → archive listing, filtered by year + category
 * GET /archiv/filter            → AJAX fragment: events grid + category chips
 * GET /events/{id}/promo-fragment
 * POST /events/add              → add event
 * POST /events/{id}/save        → update event field
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
require_once __DIR__ . '/../Models/UrlModel.php';

class EventController extends BaseController
{
    private EventModel       $eventModel;
    private ParticipantModel $participantModel;
    private MediaModel       $mediaModel;
    private PagesModel       $pagesModel;
    private VenueModel       $venueModel;
    private UrlModel         $urlModel;

    public function __construct()
    {
        parent::__construct();
        $this->eventModel       = new EventModel();
        $this->participantModel = new ParticipantModel();
        $this->mediaModel       = new MediaModel();
        $this->pagesModel       = new PagesModel();
        $this->venueModel       = new VenueModel();
        $this->urlModel         = new UrlModel();
    }

    // ── GET — display ────────────────────────────────────────

    /**
     * GET /veranstaltungen
     * Upcoming events only. All past events live on /archiv.
     */
    public function index(array $params = []): void
    {
        $sections = $this->pagesModel->getForPage('veranstaltungen');
        $upcoming = $this->eventModel->getUpcoming();

        if (!$this->isLoggedIn()) {
            $upcoming = array_filter($upcoming, fn($e) => $e->status === 'published' && empty($e->cancelled_at));
        }

        foreach ($upcoming as $event) {
            $event->slug  = EventModel::generateSlug($event->title);
            $event->promo = $this->mediaModel->getFirstForEntity('event', $event->id, 'promo');
        }

        $seo = $this->buildSeo(
            $this->org,
            $this->org->name . ' | Programm',
            'Kommende Veranstaltungen von ' . $this->org->name
        );

        $this->render('pages/events', [
            'sections' => $sections,
            'upcoming' => $upcoming,
            'seo'      => $seo,
            'pageKey'  => 'veranstaltungen',
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

        // Non-editors cannot view drafts or cancelled events
        if (!$this->isLoggedIn()) {
            if ($event->status === 'draft' || !empty($event->cancelled_at)) {
                $this->renderNotFound();
                return;
            }
        }

        $event->slug         = $slug;
        $event->temporal     = EventModel::getStatus($event);
        $event->participants = $this->participantModel->getForEvent($event->id);
        $event->venue        = !empty($event->venue_id) ? $this->venueModel->getById($event->venue_id) : null;
        $event->media        = $this->mediaModel->getForEntity('event', $event->id);
        $event->urls         = $this->urlModel->getForEntity('event', $event->id);

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
     * Archive listing — all past events, filtered by year and/or category.
     * Default year = current year (e.g. 2026), falling back to the most
     * recent year that actually has events if the current year has none yet.
     */
    public function archive(array $params = []): void
    {
        $sections = $this->pagesModel->getForPage('archiv');
        $years    = $this->eventModel->getArchiveYears();

        // Determine default year: current year if it has past events,
        // otherwise the most recent year that does.
        $availableYears = array_column($years, 'year');
        $currentYear    = (int) date('Y');
        $defaultYear    = in_array($currentYear, $availableYears)
            ? $currentYear
            : (int) ($availableYears[0] ?? $currentYear);

        $selectedYear     = isset($_GET['year']) ? (int) $_GET['year'] : $defaultYear;
        $selectedCategory = isset($_GET['category']) ? (int) $_GET['category'] : null;

        // countArchive includes drafts — used to detect unpublished events
        // for the curation notice. Fetched before the public filter strips them.
        $events              = $this->eventModel->getArchive($selectedYear, $selectedCategory);
        $totalEventsInPeriod = $this->eventModel->countArchive($selectedYear, $selectedCategory);

        $categories = $this->eventModel->getArchiveCategoriesByYear($selectedYear);

        if (!$this->isLoggedIn()) {
            $events = array_filter($events, fn($e) => $e->status === 'published' && empty($e->cancelled_at));
        }

        foreach ($events as $event) {
            $event->slug      = EventModel::generateSlug($event->title);
            // Gallery image preferred in archive — shows what actually happened.
            // Promo (often a flyer) is the fallback when no gallery exists yet.
            $event->cardImage = $this->mediaModel->getFirstForEntity('event', $event->id, 'gallery')
                ?? $this->mediaModel->getFirstForEntity('event', $event->id, 'promo');
        }

        $seo = $this->buildSeo(
            $this->org,
            $this->org->name . ' | Archiv',
            'Vergangene Veranstaltungen von ' . $this->org->name
        );

        $this->render('pages/archive', [
            'sections'            => $sections,
            'events'              => $events,
            'years'               => $years,
            'categories'          => $categories,
            'selectedYear'        => $selectedYear,
            'selectedCategory'    => $selectedCategory,
            'totalEventsInPeriod' => $totalEventsInPeriod,
            'seo'                 => $seo,
            'pageKey'             => 'archiv',
        ]);
    }

    /**
     * GET /archiv/filter
     * AJAX endpoint — returns JSON with two HTML fragments:
     *   events:     the event grid for the selected year/category
     *   categories: the category chips for the selected year
     * The year chips themselves are static PHP (never swapped by AJAX).
     */
    public function archiveFilter(array $params = []): void
    {
        $year       = (int) ($_GET['year'] ?? date('Y'));
        $categoryId = isset($_GET['category']) ? (int) $_GET['category'] : null;
        $isLoggedIn = $this->isLoggedIn();

        // countArchive includes drafts — captured before public filter.
        $events              = $this->eventModel->getArchive($year, $categoryId);
        $totalEventsInPeriod = $this->eventModel->countArchive($year, $categoryId);

        $categories = $this->eventModel->getArchiveCategoriesByYear($year);

        if (!$isLoggedIn) {
            $events = array_filter($events, fn($e) => $e->status === 'published' && empty($e->cancelled_at));
        }

        foreach ($events as $event) {
            $event->slug      = EventModel::generateSlug($event->title);
            $event->cardImage = $this->mediaModel->getFirstForEntity('event', $event->id, 'gallery')
                ?? $this->mediaModel->getFirstForEntity('event', $event->id, 'promo');
        }

        $selectedCategory = $categoryId;

        ob_start();
        include __DIR__ . '/../Views/components/archive/events-grid.php';
        $eventsHtml = ob_get_clean();

        ob_start();
        include __DIR__ . '/../Views/components/archive/categories.php';
        $categoriesHtml = ob_get_clean();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success'    => true,
            'events'     => $eventsHtml,
            'categories' => $categoriesHtml,
        ]);
    }

    /**
     * GET /events/{id}/promo-fragment
     * Re-renders the promo-media partial for in-place DOM update after
     * upload/delete — no full page reload needed.
     */
    public function promoFragment(array $params = []): void
    {
        $id    = (int) ($params['id'] ?? 0);
        $event = $this->eventModel->getById($id);

        if (!$event) {
            http_response_code(404);
            echo '';
            return;
        }

        $promoImages = $this->mediaModel->getForEntity('event', $id, 'promo');
        $isLoggedIn  = $this->isLoggedIn();

        ob_start();
        include __DIR__ . '/../Views/components/event/promo-media.php';
        $html = ob_get_clean();

        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

    // ── POST — CRUD ──────────────────────────────────────────

    public function add(array $params = []): void
    {
        $this->requireLogin();

        // Create minimal event → redirect to detail for inline editing
        $title = $_POST['title'] ?? 'Neue Veranstaltung';
        $date  = $_POST['date']  ?? date('Y-m-d');

        $id = $this->eventModel->add([
            'title' => $title,
            'date'  => $date,
        ]);

        if (!$id) {
            $this->jsonError('Failed to create event');
            return;
        }

        $event = $this->eventModel->getById($id);
        $slug  = EventModel::generateSlug($event->title);

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

        if (!$success) {
            $this->jsonError('Failed to save');
            return;
        }

        // If the title changed, the slug changes too — return it so
        // the browser URL can be updated without a page reload.
        if ($field === 'title') {
            $slug = EventModel::generateSlug($value);
            $this->jsonSuccess(['slug' => $slug]);
            return;
        }

        $this->jsonSuccess();
    }

    public function delete(array $params = []): void
    {
        $this->requireLogin();
        $id    = (int) ($params['id'] ?? 0);
        $event = $this->eventModel->getById($id);

        if (!$event) {
            $this->jsonError('Event not found');
            return;
        }

        if ($event->status !== 'draft') {
            $this->jsonError('Only draft events can be deleted');
            return;
        }

        $success = $this->eventModel->delete($id);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to delete event');
    }

    /**
     * POST /events/{id}/publish
     */
    public function publish(array $params = []): void
    {
        $this->requireLogin();
        $id      = (int) ($params['id'] ?? 0);
        $success = $this->eventModel->publish($id);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to publish event');
    }

    /**
     * POST /events/{id}/unpublish
     */
    public function unpublish(array $params = []): void
    {
        $this->requireLogin();
        $id      = (int) ($params['id'] ?? 0);
        $success = $this->eventModel->unpublish($id);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to unpublish event');
    }

    /**
     * POST /events/{id}/cancel
     * Cancel an upcoming published event — sets cancelled_at timestamp.
     */
    public function cancel(array $params = []): void
    {
        $this->requireLogin();
        $id    = (int) ($params['id'] ?? 0);
        $event = $this->eventModel->getById($id);

        if (!$event) {
            $this->jsonError('Event not found');
            return;
        }

        if ($event->status !== 'published') {
            $this->jsonError('Only published events can be cancelled');
            return;
        }

        if (!empty($event->cancelled_at)) {
            $this->jsonError('Event is already cancelled');
            return;
        }

        if (!empty($event->date) && strtotime($event->date) < strtotime('today')) {
            $this->jsonError('Past events cannot be cancelled');
            return;
        }

        $success = $this->eventModel->cancel($id);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to cancel event');
    }

    public function addParticipant(array $params = []): void
    {
        $this->requireLogin();
        $eventId       = (int) ($params['id'] ?? 0);
        $participantId = (int) ($_POST['participant_id'] ?? 0);
        $success       = $this->eventModel->addParticipant($eventId, $participantId);

        if (!$success) {
            $this->jsonError('Failed to add participant');
            return;
        }

        $participant = $this->participantModel->getById($participantId);
        $slug        = $participant ? ParticipantModel::generateSlug($participant) : '';
        $field       = $participant->field ?? '';

        $this->jsonSuccess(['slug' => $slug, 'field' => $field]);
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
