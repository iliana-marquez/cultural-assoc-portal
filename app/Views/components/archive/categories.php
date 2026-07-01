<?php

/**
 * components/archive/categories.php
 *
 * Category chip row for the archive page.
 * Reused by archive.php (full page) and EventController::archiveFilter() (AJAX).
 * Only renders when selected year has more than one category — no noise.
 *
 * Variables (in scope from parent):
 *   $categories       array       Categories present in selected year
 *   $selectedCategory int|null    Currently selected category id
 */
?>

<?php
$publishedCount = array_sum(array_column($categories, 'event_count'));
$draftCount     = ($totalEventsInPeriod ?? 0) - $publishedCount;
?>

<?php if (!empty($categories) || $draftCount > 0): ?>
    <div class="archive-categories">

        <?php if (!empty($categories)): ?>
            <?php if (count($categories) > 1): ?>
                <a href="#"
                    class="archive-category-chip<?= !$selectedCategory ? ' archive-category-chip--active' : '' ?>"
                    data-category="">
                    Alle <span class="archive-category-count">(<?= $publishedCount ?>)</span>
                </a>
            <?php endif; ?>
            <?php foreach ($categories as $cat): ?>
                <?php $isActive = $selectedCategory == $cat->id
                    || (!$selectedCategory && count($categories) === 1); ?>
                <a href="#"
                    class="archive-category-chip<?= $isActive ? ' archive-category-chip--active' : '' ?>"
                    data-category="<?= $cat->id ?>">
                    <?= htmlspecialchars($cat->label) ?>
                    <span class="archive-category-count">(<?= $cat->event_count ?>)</span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($draftCount > 0): ?>
            <span class="archive-curation-notice">
                <i class="ti ti-ad-2"></i>
                <strong><?= $draftCount ?> Veranstaltung<?= $draftCount > 1 ? 'en werden' : ' wird' ?> gerade kuratiert, schau bald wieder vorbei.</strong>
            </span>
        <?php endif; ?>

    </div>
<?php endif; ?>