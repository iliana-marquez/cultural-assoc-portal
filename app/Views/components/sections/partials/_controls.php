<?php

/**
 * _controls.php
 *
 * Edit mode controls for free sections.
 * Save/Cancel are OUTSIDE .block-edit-controls — never cloned.
 * Toggle controls inside .block-edit-controls — cloned on each activation
 * to prevent stale event listeners.
 */
?>

<div class="block-controls">

    <!-- Pencil — inactive state only -->
    <button class="section-control-btn btn-edit">
        <i class="ti ti-pencil"></i>
    </button>

    <!-- Toggle controls — cloned on each activation to clear stale listeners -->
    <div class="block-edit-controls">

        <div data-toggle="theme" data-value="<?= htmlspecialchars($section->theme ?? 'light') ?>">
            <button class="section-control-btn" data-action="toggle-theme">
                <i class="ti ti-sun"></i> Light/Dark
            </button>
        </div>

        <div data-toggle="layout" data-value="<?= htmlspecialchars($section->layout ?? '50-50') ?>">
            <button class="section-control-btn" data-action="toggle-layout">
                <i class="ti ti-layout-columns"></i>
                <span class="layout-label"><?= htmlspecialchars($section->layout ?? '50-50') ?></span>
            </button>
        </div>

        <!-- Text align — only when no image -->
        <div data-toggle="align"
            data-value="<?= htmlspecialchars($section->align ?? 'left') ?>"
            class="ctrl-no-image <?= !empty($section->image) ? 'd-none' : '' ?>">
            <button class="section-control-btn" data-action="toggle-align">
                <i class="ti ti-align-left"></i>
                <span class="align-label"><?= htmlspecialchars($section->align ?? 'left') ?></span>
            </button>
        </div>

        <!-- Flip — only when image present -->
        <div class="ctrl-has-image <?= empty($section->image) ? 'd-none' : '' ?>">
            <div data-toggle="image_pos" data-value="<?= htmlspecialchars($section->image_pos ?? 'right') ?>">
                <button class="section-control-btn" data-action="toggle-flip">
                    <i class="ti ti-arrows-left-right"></i> Flip
                </button>
            </div>
        </div>

        <!-- Fit — only when image present -->
        <div class="ctrl-has-image <?= empty($section->image) ? 'd-none' : '' ?>">
            <div data-toggle="object_fit" data-value="<?= htmlspecialchars($section->object_fit ?? 'cover') ?>">
                <button class="section-control-btn" data-action="toggle-fit">
                    <i class="ti ti-aspect-ratio"></i> Fit
                </button>
            </div>
        </div>

        <!-- Add image — only when no image -->
        <div class="ctrl-no-image <?= !empty($section->image) ? 'd-none' : '' ?>">
            <label class="section-control-btn" style="cursor:pointer;">
                <i class="ti ti-photo-plus"></i> Bild
                <input type="file" accept="image/*" class="d-none" data-action="upload-image">
            </label>
        </div>

        <!-- Remove image — only when image present -->
        <div class="ctrl-has-image <?= empty($section->image) ? 'd-none' : '' ?>">
            <button class="section-control-btn" data-action="remove-image">
                <i class="ti ti-photo-x"></i> Bild entfernen
            </button>
        </div>

        <!-- BG image -->
        <?php if (empty($section->bg_image)): ?>
            <label class="section-control-btn" style="cursor:pointer;">
                <i class="ti ti-wallpaper"></i> BG
                <input type="file" accept="image/*" class="d-none" data-action="upload-bg">
            </label>
        <?php else: ?>
            <button class="section-control-btn" data-action="remove-bg">
                <i class="ti ti-wallpaper-off"></i> BG entfernen
            </button>
        <?php endif; ?>

    </div>
    <!-- END .block-edit-controls — cloned zone ends here -->

    <!-- Save/Cancel — OUTSIDE clone zone, listeners attached once at init -->
    <button class="section-control-btn btn-save">
        <i class="ti ti-check"></i> Speichern
    </button>

    <button class="section-control-btn btn-cancel">
        <i class="ti ti-x"></i>
    </button>

    <span class="block-feedback"></span>

</div>