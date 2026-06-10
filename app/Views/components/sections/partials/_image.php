<?php

/**
 * _image.php
 *
 * Section image partial.
 * Renders image with placeholder fallback and optional credit.
 *
 * Variables from parent section:
 *   $image       string|null  URL
 *   $imageCredit string|null  Photographer credit
 *   $title       string|null  Used as alt text
 *   $objectFit   string       'cover' | 'contain'
 */
?>

<?php if (!empty($image)): ?>
    <div class="section-image-wrap">
        <img src="<?= htmlspecialchars($image) ?>"
            alt="<?= htmlspecialchars($title ?? '') ?>"
            class="section-image"
            style="object-fit: <?= htmlspecialchars($objectFit ?? 'cover') ?>;">
    </div>
    <?php if (!empty($imageCredit)): ?>
        <span class="image-credit small">
            <i class="ti ti-camera"></i>
            <?= htmlspecialchars($imageCredit) ?>
        </span>
    <?php endif; ?>
<?php else: ?>
    <div class="section-image-placeholder">
        <i class="ti ti-photo"></i>
    </div>
<?php endif; ?>