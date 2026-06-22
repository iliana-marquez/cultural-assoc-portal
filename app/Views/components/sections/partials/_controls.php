<?php

/**
 * _controls.php
 *
 * Edit mode controls for free sections.
 * Save/Cancel OUTSIDE .block-edit-controls — never cloned.
 * Toggle controls inside .block-edit-controls — cloned on each activation.
 *
 * Block types:
 *   text  → align control, no image column
 *   image → flip/fit/layout controls, image column with placeholder
 *
 * Image upload/change/delete lives in _image.php — not here.
 */

$blockType = !empty($section->image) || ($section->image_pos ?? 'none') !== 'none'
    ? 'image'
    : 'text';
?>

<div class="block-controls">

    <!-- Pencil — inactive state only -->
    <button class="section-control-btn btn-edit">
        <i class="ti ti-pencil"></i>
    </button>

    <!-- Toggle controls — cloned on each activation -->
    <div class="block-edit-controls">

        <!-- Block type toggle — add/remove image column -->
        <div data-toggle="block_type" data-value="<?= $blockType ?>">
            <button class="section-control-btn" data-action="toggle-block-type">
                <i class="ti ti-<?= $blockType === 'image' ? 'layout-sidebar-right-collapse' : 'layout-sidebar-right' ?>"></i>
                <span class="block-type-label">
                    <?= $blockType === 'image' ? 'Bildspalte entfernen' : '+ Bildspalte' ?>
                </span>
            </button>
        </div>

        <!-- Theme — always available -->
        <div data-toggle="theme" data-value="<?= htmlspecialchars($section->theme ?? 'light') ?>">
            <button class="section-control-btn" data-action="toggle-theme">
                <i class="ti ti-sun"></i> Light/Dark
            </button>
        </div>

        <!-- Text align — text block only -->
        <div data-toggle="align"
            data-value="<?= htmlspecialchars($section->align ?? 'left') ?>"
            class="ctrl-text-block <?= $blockType !== 'text' ? 'd-none' : '' ?>">
            <button class="section-control-btn" data-action="toggle-align">
                <i class="ti ti-align-left"></i>
                <span class="align-label"><?= htmlspecialchars($section->align ?? 'left') ?></span>
            </button>
        </div>

        <!-- Layout — image block only -->
        <div data-toggle="layout"
            data-value="<?= htmlspecialchars($section->layout ?? '50-50') ?>"
            class="ctrl-image-block <?= $blockType !== 'image' ? 'd-none' : '' ?>">
            <button class="section-control-btn" data-action="toggle-layout">
                <i class="ti ti-layout-columns"></i>
                <span class="layout-label"><?= htmlspecialchars($section->layout ?? '50-50') ?></span>
            </button>
        </div>

        <!-- Flip — image block only -->
        <div data-toggle="image_pos"
            data-value="<?= htmlspecialchars($section->image_pos ?? 'none') ?>"
            class="ctrl-image-block <?= $blockType !== 'image' ? 'd-none' : '' ?>">
            <button class="section-control-btn" data-action="toggle-flip">
                <i class="ti ti-arrows-left-right"></i> Flip
            </button>
        </div>

        <!-- Fit — image block only -->
        <div data-toggle="object_fit"
            data-value="<?= htmlspecialchars($section->object_fit ?? 'cover') ?>"
            class="ctrl-image-block <?= $blockType !== 'image' ? 'd-none' : '' ?>">
            <button class="section-control-btn" data-action="toggle-fit">
                <i class="ti ti-aspect-ratio"></i> Fit
            </button>
        </div>

        <!-- BG image — always available -->
        <div class="bg-btn-wrap">
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

    </div>
    <!-- END .block-edit-controls -->

    <!-- Save/Cancel — OUTSIDE clone zone -->
    <button class="section-control-btn btn-save">
        <i class="ti ti-check"></i> Speichern
    </button>

    <button class="section-control-btn btn-cancel">
        <i class="ti ti-x"></i>
    </button>

    <span class="block-feedback"></span>

</div>