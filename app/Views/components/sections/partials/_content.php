<?php

/**
 * _content.php
 *
 * Section content partial.
 * edit-field-label appears ABOVE the field it labels.
 *
 * Fields use <div> not <h2>/<p> — block-level semantic tags
 * cannot contain <ul>/<li> (HTML spec violation), which causes
 * the browser to forcibly eject list content outside the field,
 * corrupting the structure. <div> has no such constraint.
 *
 * Output is raw (no htmlspecialchars) — the stored HTML is our
 * own controlled markup (<span class="rt-bold">, <a href="...">,
 * etc.) applied via the toolbar. htmlspecialchars() would escape
 * these tags into visible literal text.
 */
?>

<div class="section-content">

    <?php if (!empty($title) || $isLoggedIn): ?>
        <?php if ($isLoggedIn): ?>
            <small class="edit-field-label">Titel</small>
        <?php endif; ?>
        <div class="section-h2" data-field="title"><?= $title ?? '' ?></div>
    <?php endif; ?>

    <?php if (!empty($subtitle) || $isLoggedIn): ?>
        <?php if ($isLoggedIn): ?>
            <small class="edit-field-label">Untertitel</small>
        <?php endif; ?>
        <div class="section-h3" data-field="subtitle"><?= $subtitle ?? '' ?></div>
    <?php endif; ?>

    <?php if (!empty($text) || $isLoggedIn): ?>
        <?php if ($isLoggedIn): ?>
            <small class="edit-field-label">Text</small>
        <?php endif; ?>
        <div class="section-p" data-field="text"><?= $text ?? '' ?></div>
    <?php endif; ?>

    <?php require __DIR__ . '/_cta-buttons.php'; ?>

</div>