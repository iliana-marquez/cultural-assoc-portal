<?php

/**
 * _content.php
 *
 * Section content partial.
 * edit-field-label appears ABOVE the field it labels.
 *
 * Content stored as raw HTML — editor produces <span class="rt-bold">,
 * <span class="rt-italic">, <a href="..."> via the rich text toolbar.
 * Output raw — no htmlspecialchars(), no marker conversion.
 * Sanitized on save via strip_tags() allowlist in PageController::saveSection().
 *
 * Fields use <div> — block-level tags like <h2>/<p> cannot contain
 * <ul>/<li> (HTML spec violation), causing browsers to eject list
 * content outside the field.
 */
?>

<div class="section-content">

    <?php if (!empty($title) || $isLoggedIn): ?>
        <?php if ($isLoggedIn): ?>
            <small class="edit-field-label">Titel</small>
        <?php endif; ?>
        <div class="section-h2" data-field="title"><?= html_entity_decode($title ?? '') ?></div>
    <?php endif; ?>

    <?php if (!empty($subtitle) || $isLoggedIn): ?>
        <?php if ($isLoggedIn): ?>
            <small class="edit-field-label">Untertitel</small>
        <?php endif; ?>
        <div class="section-h3" data-field="subtitle"><?= html_entity_decode($subtitle ?? '') ?></div>
    <?php endif; ?>

    <?php if (!empty($text) || $isLoggedIn): ?>
        <?php if ($isLoggedIn): ?>
            <small class="edit-field-label">Text</small>
        <?php endif; ?>
        <div class="section-p" data-field="text"><?= html_entity_decode($text ?? '') ?></div>
    <?php endif; ?>

    <?php require __DIR__ . '/_cta-buttons.php'; ?>

</div>