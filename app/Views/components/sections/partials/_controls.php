<?php

/**
 * _controls.php
 *
 * Edit mode controls for free sections.
 * Edit button always visible when logged in.
 * Save/Cancel + toggles only visible when block is active.
 *
 * Variables from section.php:
 *   $section     object  Section data (id, theme, layout, image_pos, object_fit)
 *   $isLoggedIn  bool
 */
?>

<!-- Edit button — always visible -->
<button class="btn-edit section-control-btn">
    <i class="ti ti-pencil"></i> Bearbeiten
</button>

<!-- Controls — hidden until block active -->
<div class="block-controls d-none">

    <!-- Toggles -->
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
            <i class="ti ti-aspect-ratio"></i> Cover/Contain
        </button>
    </div>

    <!-- Save / Cancel -->
    <button class="section-control-btn btn-save">
        <i class="ti ti-check"></i> Speichern
    </button>

    <button class="section-control-btn btn-cancel">
        <i class="ti ti-x"></i> Abbrechen
    </button>

    <span class="block-feedback"></span>

</div>