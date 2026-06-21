<?php

/**
 * components/modals/input-modal.php
 *
 * Site-wide, reusable text-input modal. Pure UI chrome — no knowledge
 * of events, media, sections, or any specific entity.
 *
 * Controlled entirely from edit-mode.js via openInputModal({...}) /
 * closeInputModal(). Any feature needing to collect a piece of text
 * (caption, credit, future free-text fields) calls openInputModal()
 * rather than building its own modal — see edit-mode.js for the
 * shared inputModal controller (#inputModal).
 *
 * Shared classes (ows-modal-*) are prefixed to avoid colliding with
 * Bootstrap's own .modal-backdrop/.modal-* classes, which are
 * already loaded and actively used on this page for carousels etc.
 *
 * Rendered once, site-wide, in main.php — not per-page, so any page's
 * JS can call openInputModal() without needing its own copy of this markup.
 */
?>
<div class="ows-modal-backdrop input-modal" id="inputModal" style="display:none;">
    <div class="ows-modal-card">
        <div class="ows-modal-header">
            <h4 class="ows-modal-title input-modal-title"></h4>
            <button class="ows-modal-close input-modal-close" aria-label="Schließen">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <textarea class="ows-modal-textarea input-modal-textarea" rows="4" placeholder=""></textarea>
        <div class="ows-modal-actions">
            <button class="section-control-btn ows-modal-btn-secondary input-modal-cancel">Abbrechen</button>
            <button class="section-control-btn ows-modal-btn-primary input-modal-confirm">Speichern</button>
        </div>
    </div>
</div>