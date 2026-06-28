<?php

/**
 * components/modals/venue-modal.php
 *
 * Two modes, controlled by JS:
 *   - edit mode   → openVenueModal('edit', venueData)
 *                   pre-filled form, no tabs, saves to existing venue
 *   - search mode → openVenueModal('search', onSelect)
 *                   search tab + neue venue tab
 */
?>
<div class="ows-modal-backdrop venue-modal" id="venueModal" style="display:none;">
    <div class="ows-modal-card">
        <div class="ows-modal-header">
            <h4 class="ows-modal-title" id="venueModalTitle">Veranstaltungsort</h4>
            <button class="ows-modal-close venue-modal-close" aria-label="Schließen">
                <i class="ti ti-x"></i>
            </button>
        </div>

        <!-- Tabs — hidden in edit mode -->
        <div class="ows-modal-tabs" id="venueModalTabs">
            <button class="ows-modal-tab ows-modal-tab--active" data-tab="venue-search">Vorhandene</button>
            <button class="ows-modal-tab" data-tab="venue-new">Neue Venue</button>
        </div>

        <!-- Search panel -->
        <div class="venue-modal-panel" data-panel="venue-search">
            <input type="text" class="ows-modal-text-input" id="venueModalSearch" placeholder="Venue suchen...">
            <div id="venueModalResults" class="attach-entity-modal-results"></div>
        </div>

        <!-- Add new panel -->
        <div class="venue-modal-panel" data-panel="venue-new" style="display:none;">
            <div class="ows-modal-field">
                <label class="ows-modal-field-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="ows-modal-text-input" id="venueModalName" placeholder="z. B. Hotel Regina, Salon Alt Wien">
            </div>
            <div class="ows-modal-field">
                <label class="ows-modal-field-label">Straße</label>
                <input type="text" class="ows-modal-text-input" id="venueModalStreet" placeholder="z. B. Rooseveltplatz 15">
            </div>
            <div class="row g-2 mb-2">
                <div class="col-4">
                    <label class="ows-modal-field-label">PLZ</label>
                    <input type="text" class="ows-modal-text-input" id="venueModalPostcode" placeholder="1090">
                </div>
                <div class="col-8">
                    <label class="ows-modal-field-label">Stadt</label>
                    <input type="text" class="ows-modal-text-input" id="venueModalCity" placeholder="Wien">
                </div>
            </div>
            <div class="ows-modal-field">
                <label class="ows-modal-field-label">Land</label>
                <input type="text" class="ows-modal-text-input" id="venueModalCountry" value="Österreich">
            </div>
            <hr>
            <p class="ows-modal-field-label mb-1">Links (optional)</p>
            <div class="ows-modal-field">
                <label class="ows-modal-field-label"><i class="ti ti-map-pin"></i> Maps URL</label>
                <input type="text" class="ows-modal-text-input" id="venueModalMapUrl" placeholder="https://maps.google.com/...">
                <a class="attach-entity-modal-test-link" id="venueModalMapUrlTest"
                    href="#" target="_blank" rel="noopener" style="display:none;">
                    Link testen <i class="ti ti-external-link"></i>
                </a>
            </div>
            <div class="ows-modal-field">
                <label class="ows-modal-field-label"><i class="ti ti-world"></i> Website URL</label>
                <input type="text" class="ows-modal-text-input" id="venueModalWebsiteUrl" placeholder="https://...">
                <a class="attach-entity-modal-test-link" id="venueModalWebsiteUrlTest"
                    href="#" target="_blank" rel="noopener" style="display:none;">
                    Link testen <i class="ti ti-external-link"></i>
                </a>
            </div>
            <div class="ows-modal-actions">
                <button class="section-control-btn ows-modal-btn-primary" id="venueModalAdd" disabled>
                    Hinzufügen
                </button>
            </div>
        </div>

        <!-- Edit existing panel — shown only in edit mode, no tabs -->
        <div class="venue-modal-panel" data-panel="venue-edit" style="display:none;">
            <input type="hidden" id="venueModalEditId">
            <div class="ows-modal-field">
                <label class="ows-modal-field-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="ows-modal-text-input" id="venueModalEditName">
            </div>
            <div class="ows-modal-field">
                <label class="ows-modal-field-label">Straße</label>
                <input type="text" class="ows-modal-text-input" id="venueModalEditStreet">
            </div>
            <div class="row g-2 mb-2">
                <div class="col-4">
                    <label class="ows-modal-field-label">PLZ</label>
                    <input type="text" class="ows-modal-text-input" id="venueModalEditPostcode">
                </div>
                <div class="col-8">
                    <label class="ows-modal-field-label">Stadt</label>
                    <input type="text" class="ows-modal-text-input" id="venueModalEditCity">
                </div>
            </div>
            <div class="ows-modal-field">
                <label class="ows-modal-field-label">Land</label>
                <input type="text" class="ows-modal-text-input" id="venueModalEditCountry">
            </div>
            <hr>
            <p class="ows-modal-field-label mb-1">Links</p>
            <div class="ows-modal-field">
                <label class="ows-modal-field-label"><i class="ti ti-map-pin"></i> Maps URL</label>
                <input type="text" class="ows-modal-text-input" id="venueModalEditMapUrl" placeholder="https://maps.google.com/...">
                <a class="attach-entity-modal-test-link" id="venueModalEditMapUrlTest"
                    href="#" target="_blank" rel="noopener" style="display:none;">
                    Link testen <i class="ti ti-external-link"></i>
                </a>
            </div>
            <div class="ows-modal-field">
                <label class="ows-modal-field-label"><i class="ti ti-world"></i> Website URL</label>
                <input type="text" class="ows-modal-text-input" id="venueModalEditWebsiteUrl" placeholder="https://...">
                <a class="attach-entity-modal-test-link" id="venueModalEditWebsiteUrlTest"
                    href="#" target="_blank" rel="noopener" style="display:none;">
                    Link testen <i class="ti ti-external-link"></i>
                </a>
            </div>
            <div class="ows-modal-actions">
                <button class="section-control-btn ows-modal-btn-primary" id="venueModalSave">
                    Speichern
                </button>
            </div>
        </div>

    </div>
</div>