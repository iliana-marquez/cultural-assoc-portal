<?php

/**
 * _content.php
 *
 * Section content partial.
 * edit-field-label appears ABOVE the field it labels.
 */
?>

<div class="section-content">

    <?php if (!empty($title) || $isLoggedIn): ?>
        <?php if ($isLoggedIn): ?>
            <small class="edit-field-label">Titel</small>
        <?php endif; ?>
        <h2 data-field="title">
            <?= htmlspecialchars($title ?? '') ?>
        </h2>
    <?php endif; ?>

    <?php if (!empty($subtitle) || $isLoggedIn): ?>
        <?php if ($isLoggedIn): ?>
            <small class="edit-field-label">Untertitel</small>
        <?php endif; ?>
        <p data-field="subtitle">
            <strong><?= htmlspecialchars($subtitle ?? '') ?></strong>
        </p>
    <?php endif; ?>

    <?php if (!empty($text) || $isLoggedIn): ?>
        <?php if ($isLoggedIn): ?>
            <small class="edit-field-label">Text</small>
        <?php endif; ?>
        <p data-field="text">
            <?= nl2br(htmlspecialchars($text ?? '')) ?>
        </p>
    <?php endif; ?>

    <?php require __DIR__ . '/_cta-buttons.php'; ?>

</div>