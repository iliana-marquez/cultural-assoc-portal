<?php

/**
 * _image.php
 *
 * Section image partial.
 * Three states:
 *   1. Has image         → image + edit overlay (logged in)
 *   2. No image, logged in → placeholder + upload btn (always rendered, JS shows/hides col)
 *   3. No image, visitor  → nothing (col never rendered for visitors)
 *
 * Note: PHP cannot know if editor will toggle to image block later.
 * Placeholder always rendered when logged in — JS controls col visibility.
 */
?>

<?php if (!empty($image)): ?>

    <!-- Has image -->
    <div class="section-image-wrap">
        <img src="<?= htmlspecialchars($image) ?>"
            alt="<?= htmlspecialchars($title ?? '') ?>"
            class="section-image"
            style="object-fit: <?= htmlspecialchars($objectFit ?? 'cover') ?>;">

        <?php if ($isLoggedIn): ?>
            <div class="image-edit-overlay">
                <label class="section-control-btn" style="cursor:pointer;">
                    <i class="ti ti-refresh"></i> Ändern
                    <input type="file" accept="image/*" class="d-none" data-action="upload-image">
                </label>
                <button class="section-control-btn" data-action="remove-image">
                    <i class="ti ti-trash"></i> Entfernen
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($imageCredit)): ?>
        <span class="image-credit small">
            <i class="ti ti-camera"></i>
            <?= htmlspecialchars($imageCredit) ?>
        </span>
    <?php endif; ?>

<?php elseif ($isLoggedIn): ?>

    <!-- No image — placeholder with upload btn (always for logged in editors) -->
    <div class="section-image-placeholder">
        <i class="ti ti-photo"></i>
        <label class="section-control-btn placeholder-upload-btn" style="cursor:pointer;">
            <i class="ti ti-photo-plus"></i> Bild hinzufügen
            <input type="file" accept="image/*" class="d-none" data-action="upload-image">
        </label>
    </div>

<?php endif; ?>