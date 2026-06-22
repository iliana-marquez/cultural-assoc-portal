<?php

/**
 * archive.php
 *
 * Archive listing — events before 2025.
 * Same event-card component as events listing.
 * Data quality varies — some fields may be null.
 *
 * Variables:
 *   $sections array  Free sections from PagesModel
 *   $events   array  Archive events from EventModel::getArchive()
 */
?>

<?php $sectionsMode = 'intro'; ?>
<?php require __DIR__ . '/../components/sections/render-sections.php'; ?>

<section class="segment light-segment">
    <div class="container">

        <?php if (empty($events)): ?>
            <p>Inhalt folgt in Kürze.</p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($events as $event): ?>
                    <?php require __DIR__ . '/../components/event/event-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php $sectionsMode = 'rest'; ?>
<?php require __DIR__ . '/../components/sections/render-sections.php'; ?>