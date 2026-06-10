<?php

/**
 * home.php
 *
 * Homepage view.
 * Hero — fixed, driven by organisation_info ($org from BaseController)
 * Sections — free, from pages table via PagesModel
 */
?>

<?php require __DIR__ . '/../components/hero.php'; ?>

<?php if (!empty($sections)): ?>
    <?php require __DIR__ . '/../components/sections/render-sections.php'; ?>
<?php endif; ?>