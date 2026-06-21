<?php

/**
 * components/modals/attach-entity-modal.php
 *
 * Site-wide, reusable "search existing or add new" modal shell.
 * No hardcoded knowledge of any specific entity — configured at
 * open time via openAttachEntityModal({...}) in edit-mode.js.
 *
 * Used for any entity that's both numerous enough to need search
 * (rather than a plain <select>) and creatable inline without
 * leaving the current page: URLs, venues, participants, and
 * future entities following the same pattern.
 *
 * The "add new" panel's fields are rendered dynamically by JS,
 * based on the calling config's addFields list — this file only
 * provides the empty container they get rendered into.
 *
 * Shared classes (ows-modal-*) are prefixed to avoid colliding with
 * Bootstrap's own .modal-backdrop/.modal-* classes, which are
 * already loaded and actively used on this page for carousels etc.
 *
 * Rendered once, site-wide, in main.php — not per-page.
 */
?>
<div class="ows-modal-backdrop attach-entity-modal" id="attachEntityModal" style="display:none;">
    <div class="ows-modal-card">
        <div class="ows-modal-header">
            <h4 class="ows-modal-title">Auswählen</h4>
            <button class="ows-modal-close" aria-label="Schließen">
                <i class="ti ti-x"></i>
            </button>
        </div>

        <div class="attach-entity-modal-tabs">
            <button class="attach-entity-modal-tab attach-entity-modal-tab--active" data-tab="search">Vorhandene</button>
            <button class="attach-entity-modal-tab" data-tab="new">Neu hinzufügen</button>
        </div>

        <!-- Search panel -->
        <div class="attach-entity-modal-panel" data-panel="search">
            <input type="text" class="ows-modal-text-input attach-entity-modal-search" placeholder="Suchen...">
            <div class="attach-entity-modal-results"></div>
        </div>

        <!-- Add new panel — fields injected dynamically by JS -->
        <div class="attach-entity-modal-panel" data-panel="new" style="display:none;">
            <div class="attach-entity-modal-fields"></div>
            <div class="attach-entity-modal-preview" style="display:none;">
                <span class="attach-entity-modal-preview-text"></span>
                <a class="attach-entity-modal-test-link" href="#" target="_blank" rel="noopener" style="display:none;">
                    Testen <i class="ti ti-external-link"></i>
                </a>
            </div>
            <button class="section-control-btn ows-modal-btn-primary">Hinzufügen</button>
        </div>
    </div>
</div>