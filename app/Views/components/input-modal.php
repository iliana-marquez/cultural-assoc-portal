<?php

/**
 * components/input-modal.php
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
 * Rendered once, site-wide, in main.php — not per-page, so any page's
 * JS can call openInputModal() without needing its own copy of this markup.
 */
?>
<div class="input-modal" id="inputModal" style="display:none;">
    <div class="input-modal-inner">
        <h4 class="input-modal-title"></h4>
        <textarea class="input-modal-textarea" rows="4" placeholder=""></textarea>
        <div class="input-modal-actions">
            <button class="section-control-btn input-modal-cancel">Abbrechen</button>
            <button class="section-control-btn input-modal-confirm">Speichern</button>
        </div>
    </div>
</div>