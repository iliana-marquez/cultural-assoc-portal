<?php

/**
 * events.php
 *
 * Event listing — upcoming and past sections.
 * Free intro sections from pages table above.
 *
 * Variables:
 *   $sections   array  Free sections from PagesModel
 *   $upcoming   array  Upcoming events from EventModel
 *   $past       array  Past events from EventModel
 *   $categories array  Event categories for filtering
 */
?>

<?php if (!empty($sections)): ?>
    <?php require __DIR__ . '/../components/sections/render-sections.php'; ?>
<?php endif; ?>

<section class="segment light-segment">
    <div class="container">

        <!-- Upcoming events -->
        <?php if (!empty($upcoming)): ?>
            <h2 class="events-heading">Kommende Veranstaltungen</h2>
            <div class="row g-4">
                <?php foreach ($upcoming as $event): ?>
                    <?php require __DIR__ . '/../components/event-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Past events -->
        <?php if (!empty($past)): ?>
            <h2 class="events-heading">Vergangene Veranstaltungen</h2>
            <div class="row g-4">
                <?php foreach ($past as $event): ?>
                    <?php require __DIR__ . '/../components/event-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($upcoming) && empty($past)): ?>
            <p>Keine Veranstaltungen gefunden.</p>
        <?php endif; ?>

    </div>
</section>