<?php

/**
 * components/sections/partials/_add-section-trigger.php
 *
 * Renders one "+ Abschnitt hinzufügen" trigger — a wide, full-width
 * block, not a small floating icon, so it reads as a clear, self-
 * explanatory affordance and doesn't visually compete with each
 * section's own pencil/cancel controls once editing is active.
 *
 * Placed before the first section, between every consecutive pair,
 * and after the last — render-sections.php inserts one of these at
 * each such point, each one knowing exactly which order_index values
 * it sits between (or before/after, at the page's edges).
 *
 * Required variables:
 *   $pageKey       string   which page this section belongs to
 *   $afterIndex    int|null  order_index of the section immediately
 *                            BEFORE this slot, or null if this is
 *                            the very first slot on the page
 *   $beforeIndex   int|null  order_index of the section immediately
 *                            AFTER this slot, or null if this is the
 *                            very last slot on the page
 *   $label         string   optional, defaults to "Abschnitt
 *                            hinzufügen" — used for the reserved
 *                            order_index=0 "Einleitung hinzufügen"
 *                            slot on listing/fixed-structure pages
 *
 * Deliberately a single, isolated trigger — clicking it calls one
 * function (triggerAddSection in edit-mode.js) that does the whole
 * job in v1 (immediate creation with sensible defaults). In v2,
 * that SAME function's body becomes the natural place to open a
 * thumbnail/layout picker first — no changes needed here, in the
 * markup, to support that later.
 */
$label = $label ?? 'Abschnitt hinzufügen';
?>
<div class="add-section-trigger-wrap">
    <button class="add-section-trigger"
        data-action="add-section"
        data-page-key="<?= htmlspecialchars($pageKey) ?>"
        data-after-index="<?= $afterIndex ?? '' ?>"
        data-before-index="<?= $beforeIndex ?? '' ?>">
        <i class="ti ti-plus"></i> <?= htmlspecialchars($label) ?>
    </button>
</div>