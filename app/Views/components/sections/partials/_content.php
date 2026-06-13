<?php

/**
 * _content.php
 *
 * Section content partial.
 * data-field attributes enable contenteditable in edit mode.
 *
 * Variables from parent section:
 *   $title      string|null
 *   $subtitle   string|null
 *   $text       string|null
 *   $cta        object|null  { label, url }
 *   $isLoggedIn bool
 */
?>

<div class="section-content">

    <?php if (!empty($title) || $isLoggedIn): ?>
        <h2 data-field="title"
            <?= $isLoggedIn ? 'contenteditable="false"' : '' ?>>
            <?= htmlspecialchars($title ?? '') ?>
        </h2>
    <?php endif; ?>

    <?php if (!empty($subtitle) || $isLoggedIn): ?>
        <p data-field="subtitle"
            <?= $isLoggedIn ? 'contenteditable="false"' : '' ?>>
            <strong><?= htmlspecialchars($subtitle ?? '') ?></strong>
        </p>
    <?php endif; ?>

    <?php if (!empty($text) || $isLoggedIn): ?>
        <p data-field="text"
            <?= $isLoggedIn ? 'contenteditable="false"' : '' ?>>
            <?= nl2br(htmlspecialchars($text ?? '')) ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($cta)): ?>
        <a href="<?= htmlspecialchars($cta->url) ?>"
            class="btn-section <?= $ctaAlignClass ?? 'align-self-start' ?>">
            <?= htmlspecialchars($cta->label) ?>
        </a>
    <?php endif; ?>

</div>