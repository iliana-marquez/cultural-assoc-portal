<?php

/**
 * free-page.php
 *
 * Universal view for all free-section pages.
 * Used by PageController::show() for all non-entity pages.
 *
 * Variables:
 *   $sections array   From PagesModel::getForPage()
 *   $pageKey  string  Current page identifier
 *   $seo      array   SEO data from PageController
 */
?>

<?php if (!empty($sections)): ?>
    <?php require __DIR__ . '/../components/sections/render-sections.php'; ?>
<?php else: ?>
    <section class="segment light-segment">
        <div class="container">
            <p>Inhalt folgt in Kürze.</p>
            <?php if ($isLoggedIn): ?>
                <button class="btn-section" id="add-first-section">
                    <i class="ti ti-plus"></i> Ersten Abschnitt hinzufügen
                </button>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>