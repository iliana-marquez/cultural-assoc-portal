<?php

/**
 * components/archive/events-grid.php
 *
 * Event grid fragment for the archive page.
 * Reused by archive.php (full page) and EventController::archiveFilter() (AJAX).
 * Uses $event->cardImage (gallery-first) with $event->promo as fallback.
 *
 * Variables (in scope from parent):
 *   $events      array   Filtered archive events
 *   $isLoggedIn  bool    From BaseController or archiveFilter()
 */
?>

<?php if (!empty($events)): ?>
    <div class="row g-4 mt-2">
        <?php foreach ($events as $event): ?>
            <?php require __DIR__ . '/../event/event-card.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>