<?php

/**
 * _controls.php
 *
 * Edit mode controls for free sections.
 *
 * Two distinct groups, deliberately separated by purpose:
 *
 *   .block-structural-controls — pencil, move up, move down, delete.
 *     VISIBLE ONLY WHEN NOT EDITING. These are structural, one-click
 *     actions (repositioning, removing) that are CORE to managing a
 *     free-section page, not secondary actions hidden behind a
 *     commitment to edit content — unlike captions/participants/
 *     links elsewhere, where editing IS secondary to the entity's
 *     main purpose. Move chevrons are conditionally rendered: a
 *     section never gets an "up" chevron if it's already first in
 *     the reorderable sequence (or "down" if already last), and the
 *     hero / reserved intro slot never get any of these controls
 *     at all, since they're outside the reorderable sequence
 *     entirely.
 *
 *   .block-edit-controls + Save/Cancel — VISIBLE ONLY WHILE EDITING.
 *     Content/layout toggles, cloned on each activation to clear
 *     stale listeners. The feedback span sits on the OPPOSITE side
 *     of this same row from the controls themselves.
 *
 * Image upload/change/delete lives in _image.php — not here.
 */

$blockType = !empty($section->image) || ($section->image_pos ?? 'none') !== 'none'
    ? 'image'
    : 'text';

$canMoveUp   = $canMoveUp ?? false;
$canMoveDown = $canMoveDown ?? false;
?>

<!-- Structural controls — pencil/move/delete — visible only
     when NOT editing. Hidden entirely while editing via CSS. -->
<div class="block-structural-controls">

    <button class="section-control-btn btn-edit">
        <i class="ti ti-pencil"></i>
    </button>

    <?php if ($canMoveUp): ?>
        <button class="section-control-btn btn-move-up" data-action="move-section-up" data-section-id="<?= $section->id ?? '' ?>">
            <i class="ti ti-chevron-up"></i>
        </button>
    <?php endif; ?>

    <?php if ($canMoveDown): ?>
        <button class="section-control-btn btn-move-down" data-action="move-section-down" data-section-id="<?= $section->id ?? '' ?>">
            <i class="ti ti-chevron-down"></i>
        </button>
    <?php endif; ?>

    <button class="section-control-btn btn-delete-section" data-action="delete-section" data-section-id="<?= $section->id ?? '' ?>">
        <i class="ti ti-trash"></i>
    </button>

</div>

<span class="block-feedback"></span>
<!-- Editing-mode row — visible only WHILE editing. Feedback span
     sits on the opposite side of this row from the controls. -->
<div class="block-controls">


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

        <!-- Text formatting — operates on whatever text field is
             currently focused/selected on the page, applied live
             in the contenteditable DOM. The editor never sees
             marker syntax; conversion to/from the safe storage
             format happens only at save/load time, via
             RichTextFormatter. Extracted to its own partial since
             ANY entity-edit-row (org-info, event, team, not just
             sections) can include it identically. -->
        <?php include __DIR__ . '/../../_richtext-toolbar.php'; ?>

    </div>
    <!-- END .block-edit-controls -->

    <!-- Save/Cancel — OUTSIDE clone zone -->
    <div class="block-edit-controls">
        <button class="section-control-btn btn-save">
            <i class="ti ti-check"></i> Speichern
        </button>

        <button class="section-control-btn btn-cancel">
            <i class="ti ti-x"></i>
        </button>
    </div>

</div>