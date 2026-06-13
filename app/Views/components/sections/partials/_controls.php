<?php

/**
 * _controls.php
 *
 * Edit mode controls for free sections.
 * Inactive: only pencil Edit button visible in neutral grey
 * Active:   all controls visible in indigo
 *
 * Activation: click block → edit-mode.js adds .editing class
 */
?>

<!-- Edit indicator — always visible, neutral grey -->
<div class="block-controls">

    <!-- Pencil — visible when inactive -->
    <button class="section-control-btn btn-edit">
        <i class="ti ti-pencil"></i>
    </button>

    <!-- Toggle controls — visible when active -->
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

        <div data-toggle="image_pos" data-value="<?= htmlspecialchars($section->image_pos ?? 'none') ?>">
            <button class="section-control-btn" data-action="toggle-flip">
                <i class="ti ti-arrows-left-right"></i> Flip
            </button>
        </div>

        <div data-toggle="object_fit" data-value="<?= htmlspecialchars($section->object_fit ?? 'cover') ?>">
            <button class="section-control-btn" data-action="toggle-fit">
                <i class="ti ti-aspect-ratio"></i> Fit
            </button>
        </div>

        <button class="section-control-btn btn-save">
            <i class="ti ti-check"></i> Speichern
        </button>

        <button class="section-control-btn btn-cancel">
            <i class="ti ti-x"></i>
        </button>

        <span class="block-feedback"></span>

    </div>

</div>