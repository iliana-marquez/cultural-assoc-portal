<?php

/**
 * free-page.php
 *
 * Universal view for all free-section pages.
 * Used by PageController::show() for all pages.
 * Hero section handled via section type 'hero' in pages table.
 *
 * Variables:
 *   $sections array   From PagesModel::getForPage()
 *   $pageKey  string  Current page identifier
 *   $seo      array   SEO data from PageController
 */
?>

<?php if (empty($sections)): ?>
    <section class="segment light-segment">
        <div class="container">
            <p>Inhalt folgt in Kürze.</p>
        </div>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/../components/sections/render-sections.php'; ?>