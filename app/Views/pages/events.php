<?php

/**
 * events.php
 *
 * Upcoming events listing — Kommende Veranstaltungen.
 * All past events live on /archiv.
 * Free intro sections from pages table above.
 *
 * Variables:
 *   $sections  array  Free sections from PagesModel
 *   $upcoming  array  Upcoming events from EventModel::getUpcoming()
 */
?>

<?php $sectionsMode = 'intro'; ?>
<?php require __DIR__ . '/../components/sections/render-sections.php'; ?>

<section class="segment light-segment">
    <div class="container">



        <?php if (!empty($upcoming)): ?>
            <!-- <p class="text-center mt-5">Als Erste:r von neuen Veranstaltungen, Künstler:innen und Neuigkeiten mit dem Newsletter erfahren</p>
            <?php require __DIR__ . '/../components/newsletter-strip.php'; ?> -->
            <div class="row g-4">
                <?php foreach ($upcoming as $event): ?>
                    <?php require __DIR__ . '/../components/event/event-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>

            <!-- <p class="text-center">Die nächste Veranstaltung ist bereits in Planung!<br>Melden Sie sich zum Newsletter an und</p> -->
            <?php require __DIR__ . '/../components/newsletter-strip.php'; ?>
        <?php endif; ?>

    </div>
</section>

<?php $sectionsMode = 'rest'; ?>
<?php require __DIR__ . '/../components/sections/render-sections.php'; ?>