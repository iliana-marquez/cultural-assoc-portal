<?php

/**
 * _content.php
 *
 * Section content partial.
 * Renders title, subtitle, text and optional CTA button.
 *
 * Variables from parent section:
 *   $title    string|null
 *   $subtitle string|null
 *   $text     string|null
 *   $cta      object|null  { label, url }
 */
?>

<div class="section-content">
    <?php if (!empty($title)): ?>
        <h2><?= htmlspecialchars($title) ?></h2>
    <?php endif; ?>

    <?php if (!empty($subtitle)): ?>
        <p><strong><?= htmlspecialchars($subtitle) ?></strong></p>
    <?php endif; ?>

    <?php if (!empty($text)): ?>
        <p><?= nl2br(htmlspecialchars($text)) ?></p>
    <?php endif; ?>

    <?php if (!empty($cta)): ?>
        <a href="<?= htmlspecialchars($cta->url) ?>"
            class="btn-section <?= $ctaAlignClass ?>">
            <?= htmlspecialchars($cta->label) ?>
        </a>
    <?php endif; ?>
</div>