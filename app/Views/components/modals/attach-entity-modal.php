<?php

/**
 * components/modals/attach-entity-modal.php
 *
 * Site-wide, reusable "search existing or add new" modal shell.
 * No hardcoded knowledge of any specific entity — configured at
 * open time via openAttachEntityModal({...}) in edit-mode.js.
 *
 * Supports three possible tabs/panels — a caller's config decides
 * which ones to show via the `tabs` option:
 *   'search' — search existing records (the original default)
 *   'new'    — add a new record, fields rendered dynamically
 *   'page'   — pick one of this site's own real pages (e.g. for
 *              a free section's CTA, or an internal-link choice);
 *              populated from GET /urls/named-pages
 * A caller omits whichever tabs don't apply to it — e.g. a plain
 * Links-list entity never requests 'page', since "link to a page
 * on this site" isn't a meaningful concept there.
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

        <div class="ows-modal-tabs">
            <button class="ows-modal-tab ows-modal-tab--active" data-tab="search">Vorhandene</button>
            <button class="ows-modal-tab" data-tab="new">Neu hinzufügen</button>
            <button class="ows-modal-tab" data-tab="page">Seite hier</button>
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
                    Link Testen <i class="ti ti-external-link"></i>
                </a>
            </div>
            <div class="ows-modal-actions">
                <button class="section-control-btn ows-modal-btn-primary">Hinzufügen</button>
            </div>
        </div>

        <!-- Page panel — pick a real, known site page. Options
             populated by JS from GET /urls/named-pages right
             before this panel is shown. The page-select sits
             alongside whichever OTHER fields this call's config
             still needs (e.g. a CTA's button-text label) — those
             render into the SAME .attach-entity-modal-fields
             container the "new" panel uses, so a config combining
             a page-select with a text field works without any
             separate field-rendering path. -->
        <div class="attach-entity-modal-panel" data-panel="page" style="display:none;">
            <small class="ows-modal-field-label">Seite</small>
            <select class="ows-modal-text-input attach-entity-modal-page-select">
                <option value="">— Seite auswählen —</option>
            </select>
            <div class="attach-entity-modal-page-fields"></div>
            <div class="attach-entity-modal-preview" data-preview="page" style="display:none;">
                <span class="attach-entity-modal-preview-text"></span>
                <a class="attach-entity-modal-test-link" href="#" target="_blank" rel="noopener" style="display:none;">
                    Link Testen <i class="ti ti-external-link"></i>
                </a>
            </div>
            <div class="ows-modal-actions">
                <button class="section-control-btn ows-modal-btn-primary">Hinzufügen</button>
            </div>
        </div>
    </div>
</div>