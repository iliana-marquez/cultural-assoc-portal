<?php

/**
 * components/modals/confirm-modal.php
 *
 * Site-wide, reusable confirmation modal. Replaces the browser's
 * native confirm() for any destructive or consequential action —
 * event deletion, removing a venue/url/participant that would
 * orphan and delete the underlying record, etc.
 *
 * Pure UI chrome — no knowledge of what it's confirming. Controlled
 * entirely from edit-mode.js via openConfirmModal({...}) /
 * closeConfirmModal().
 *
 * Unlike a plain yes/no, the confirm button's label is configurable
 * per use — naming the actual consequence ("Veranstaltung löschen",
 * "Endgültig entfernen") rather than a generic "OK", so the editor
 * always sees what they're actually about to do, not just "are you sure?".
 *
 * No separate "danger" color — consistent with the rest of the
 * editor's two-color system (edit-active / edit-inactive). The
 * button's label carries the meaning, not an alarm color.
 *
 * Shared classes (ows-modal-*) are prefixed to avoid colliding with
 * Bootstrap's own .modal-backdrop/.modal-* classes, which are
 * already loaded and actively used on this page for carousels etc.
 *
 * Rendered once, site-wide, in main.php — not per-page.
 */
?>
<div class="ows-modal-backdrop confirm-modal" id="confirmModal" style="display:none;">
    <div class="ows-modal-card">
        <div class="ows-modal-header">
            <h4 class="ows-modal-title confirm-modal-title"></h4>
            <button class="ows-modal-close confirm-modal-close" aria-label="Schließen">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <p class="confirm-modal-message"></p>
        <div class="ows-modal-actions">
            <button class="section-control-btn ows-modal-btn-secondary confirm-modal-cancel">Abbrechen</button>
            <button class="section-control-btn ows-modal-btn-primary confirm-modal-confirm"></button>
        </div>
    </div>
</div>