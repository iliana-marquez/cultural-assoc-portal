<?php

/**
 * archive.php
 *
 * Archive listing — all past events, filtered by year and category.
 * Default year = current year (or most recent year with events).
 * Year timeline and category chips filter via AJAX — no page reload.
 * Gallery image used on cards when available; promo as fallback.
 *
 * Variables:
 *   $sections         array       Free sections from PagesModel
 *   $events           array       Archive events (filtered)
 *   $years            array       Distinct years with past events
 *   $categories       array       Categories present in selected year
 *   $selectedYear     int         Currently selected year
 *   $selectedCategory int|null    Currently selected category id
 */
?>

<?php $sectionsMode = 'intro'; ?>
<?php require __DIR__ . '/../components/sections/render-sections.php'; ?>

<section class="segment light-segment">
    <div class="container">

        <?php if (!empty($years)): ?>

            <!-- Year timeline nav -->
            <nav class="archive-timeline" aria-label="Archiv nach Jahr">
                <?php foreach ($years as $i => $yearRow): ?>
                    <?php if ($i > 0): ?>
                        <i class="ti ti-arrow-narrow-left archive-timeline-sep" aria-hidden="true"></i>
                    <?php endif; ?>
                    <a href="/archiv?year=<?= $yearRow->year ?>"
                        class="archive-year-chip<?= $yearRow->year == $selectedYear ? ' archive-year-chip--active' : '' ?>"
                        data-year="<?= $yearRow->year ?>">
                        <?= $yearRow->year ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <!-- Category chips — only when selected year has multiple categories -->
            <div id="archive-categories">
                <?php include __DIR__ . '/../components/archive/categories.php'; ?>
            </div>

            <!-- Event grid -->
            <div id="archive-grid">
                <?php include __DIR__ . '/../components/archive/events-grid.php'; ?>
            </div>

        <?php else: ?>
            <p class="text-muted">Inhalt folgt in Kürze.</p>
        <?php endif; ?>

    </div>
</section>

<?php $sectionsMode = 'rest'; ?>
<?php require __DIR__ . '/../components/sections/render-sections.php'; ?>