<?php

/**
 * _image.php — Section image partial.
 * Uses .img-placeholder.block-img for consistent dimensions.
 */
?>

<?php if (!empty($image)): ?>

    <div class="img-placeholder block-img">
        <img src="<?= htmlspecialchars($image) ?>"
            alt="<?= htmlspecialchars($title ?? '') ?>"
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

    <div class="img-placeholder block-img">
        <i class="ti ti-photo"></i>
        <label class="section-control-btn placeholder-upload-btn" style="cursor:pointer;">
            <i class="ti ti-photo-plus"></i> Bild hinzufügen
            <input type="file" accept="image/*" class="d-none" data-action="upload-image">
        </label>
    </div>

<?php endif; ?>