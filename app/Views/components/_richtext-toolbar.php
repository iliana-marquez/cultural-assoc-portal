<?php

/**
 * components/_richtext-toolbar.php
 *
 * The text-formatting toolbar — Bold, Italic, Bulleted List,
 * Numbered List, Link. Entity-agnostic: this exact markup works
 * identically wherever it's included (a free section's controls,
 * org-info's entity-edit-row, an event description field, a team
 * bio field) since the JS behind it (richtext-toolbar.js) operates
 * on whichever [data-field].editable-field element currently has
 * focus anywhere on the page — never tied to one specific entity
 * or row.
 *
 * No required variables — this partial needs nothing from its
 * caller at all, since it has no per-instance state of its own.
 *
 * Usage in any entity-edit-row's controls markup:
 *   <?php include __DIR__ . '/../_richtext-toolbar.php'; ?>
 *   (adjust the relative path depth to wherever it's included from)
 */
?>
<div class="richtext-toolbar">
    <button class="section-control-btn" data-action="richtext-bold" title="Fett">
        <i class="ti ti-bold"></i>
    </button>
    <button class="section-control-btn" data-action="richtext-italic" title="Kursiv">
        <i class="ti ti-italic"></i>
    </button>
    <button class="section-control-btn" data-action="richtext-bullet-list" title="Aufzählung">
        <i class="ti ti-list"></i>
    </button>
    <button class="section-control-btn" data-action="richtext-numbered-list" title="Nummerierte Liste">
        <i class="ti ti-list-numbers"></i>
    </button>
    <button class="section-control-btn" data-action="richtext-link" title="Link einfügen">
        <i class="ti ti-link"></i>
    </button>
</div>