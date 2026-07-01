<?php

/**
 * _content.php
 *
 * Section content partial.
 * edit-field-label appears ABOVE the field it labels.
 *
 * Content stored as HTML-encoded string in JSON column.
 * html_entity_decode() converts &lt;span&gt; back to <span> before output.
 *
 * Visibility check uses strip_tags()+trim() rather than empty() —
 * the rich text editor can save "visually empty" markup like
 * <div><br></div> or &nbsp;, which empty() would treat as non-empty
 * and render a blank block on the public side.
 */

$hasTitle    = trim(strip_tags(html_entity_decode($title ?? '')))    !== '';
$hasSubtitle = trim(strip_tags(html_entity_decode($subtitle ?? ''))) !== '';
$hasText     = trim(strip_tags(html_entity_decode($text ?? '')))     !== '';
?>

<div class="section-content">

    <?php if ($hasTitle || $isLoggedIn): ?>
        <?php if ($isLoggedIn): ?>
            <small class="edit-field-label">Titel</small>
        <?php endif; ?>
        <div class="section-h2" data-field="title"><?= html_entity_decode($title ?? '') ?></div>
    <?php endif; ?>

    <?php if ($hasSubtitle || $isLoggedIn): ?>
        <?php if ($isLoggedIn): ?>
            <small class="edit-field-label">Untertitel</small>
        <?php endif; ?>
        <div class="section-h3" data-field="subtitle"><?= html_entity_decode($subtitle ?? '') ?></div>
    <?php endif; ?>

    <?php if ($hasText || $isLoggedIn): ?>
        <?php if ($isLoggedIn): ?>
            <small class="edit-field-label">Text</small>
        <?php endif; ?>
        <div class="section-p" data-field="text"><?= html_entity_decode($text ?? '') ?></div>
    <?php endif; ?>

    <?php require __DIR__ . '/_cta-buttons.php'; ?>

</div>