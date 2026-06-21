// ── attach_entity_modal ─────────────────────────────────────
// Single, generic owner of #attachEntityModal. Searches existing
// records or creates a new one, for whichever entity kind the
// caller configures — no hardcoded knowledge of urls, venues,
// participants, or anything else.
//
// Usage:
//   openAttachEntityModal({
//       title: 'Veranstaltungsort hinzufügen',
//       searchEndpoint: '/venues/search',
//       addEndpoint: '/venues/add',
//       searchPlaceholder: 'Veranstaltungsort suchen...',
//       addFields: [
//           { name: 'name', label: 'Name', type: 'text', required: true },
//           { name: 'street', label: 'Straße', type: 'text' },
//           ...
//       ],
//       extraAddParams: { entity_type: 'event', entity_id: 5 },  // optional,
//           merged into the add-new POST body — e.g. URLs need this so
//           /urls/add can attach in the same request; venues/participants
//           simply omit it.
//       renderResultItem: function (result) {
//           return result.name;  // or richer HTML
//       },
//       onSelected: function (result) {
//           // caller's own DOM update — e.g. add <option> to a <select>
//           // and select it, or append a new item to a list.
//           // For entity kinds needing an extra "attach" step (URLs),
//           // the caller's onSelected is responsible for making that
//           // second request itself — the shell has no opinion on it.
//       }
//   });
//
// Optional, URL-specific: pass `previewFn: function(fields) -> string`
// to show a live normalized preview + "Testen" link as the editor
// types in the add-new form. Omit entirely for entities that don't
// need this (venues, participants).

const attachEntityModal = {
    el: document.getElementById('attachEntityModal'),
    bound: false,
    config: null,
    searchTimer: null
};

function bindAttachEntityModalOnce() {
    if (attachEntityModal.bound || !attachEntityModal.el) return;
    attachEntityModal.bound = true;

    const closeBtn = attachEntityModal.el.querySelector('.ows-modal-close');
    const tabs = attachEntityModal.el.querySelectorAll('.attach-entity-modal-tab');
    const searchInput = attachEntityModal.el.querySelector('.attach-entity-modal-search');
    const resultsBox = attachEntityModal.el.querySelector('.attach-entity-modal-results');
    const fieldsBox = attachEntityModal.el.querySelector('.attach-entity-modal-fields');
    const previewBox = attachEntityModal.el.querySelector('.attach-entity-modal-preview');
    const previewText = attachEntityModal.el.querySelector('.attach-entity-modal-preview-text');
    const testLink = attachEntityModal.el.querySelector('.attach-entity-modal-test-link');
    const confirmNewBtn = attachEntityModal.el.querySelector('.ows-modal-btn-primary');

    closeBtn?.addEventListener('click', closeAttachEntityModal);
    attachEntityModal.el.addEventListener('click', function (e) {
        if (e.target === attachEntityModal.el) closeAttachEntityModal();
    });

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(function (t) { t.classList.remove('attach-entity-modal-tab--active'); });
            tab.classList.add('attach-entity-modal-tab--active');
            attachEntityModal.el.querySelectorAll('.attach-entity-modal-panel').forEach(function (panel) {
                panel.style.display = panel.dataset.panel === tab.dataset.tab ? 'block' : 'none';
            });
        });
    });

    searchInput?.addEventListener('input', function () {
        clearTimeout(attachEntityModal.searchTimer);
        const query = this.value.trim();
        const config = attachEntityModal.config;
        if (!config) return;
        if (query === '') {
            resultsBox.innerHTML = '';
            return;
        }
        attachEntityModal.searchTimer = setTimeout(function () {
            fetch(config.searchEndpoint + '?q=' + encodeURIComponent(query), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.json())
                .then(function (json) {
                    resultsBox.innerHTML = '';
                    const results = json.results || json.urls || json.venues || json.participants || [];
                    if (!json.success || results.length === 0) {
                        resultsBox.innerHTML = '<p class="attach-entity-modal-empty">Keine Treffer.</p>';
                        return;
                    }
                    results.forEach(function (result) {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'attach-entity-modal-result';
                        item.innerHTML = config.renderResultItem
                            ? config.renderResultItem(result)
                            : (result.label || result.name || result.url || '');
                        item.addEventListener('click', function () {
                            if (typeof config.onSelected === 'function') {
                                config.onSelected(result);
                            }
                            closeAttachEntityModal();
                        });
                        resultsBox.appendChild(item);
                    });
                })
                .catch(function () {
                    resultsBox.innerHTML = '<p class="attach-entity-modal-empty">Verbindungsfehler.</p>';
                });
        }, 300);
    });

    function renderFields(fields) {
        fieldsBox.innerHTML = '';
        (fields || []).forEach(function (field) {
            const label = document.createElement('label');
            label.className = 'attach-entity-modal-label';
            label.textContent = field.label + (field.required ? '' : ' (optional)');
            fieldsBox.appendChild(label);

            let input;
            if (field.type === 'select') {
                input = document.createElement('select');
                input.className = 'ows-modal-text-input attach-entity-modal-field';
                (field.options || []).forEach(function (opt) {
                    const option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.label;
                    input.appendChild(option);
                });
                if (field.value !== undefined) input.value = field.value;
            } else {
                input = document.createElement('input');
                input.type = field.type || 'text';
                input.className = 'ows-modal-text-input attach-entity-modal-field';
                if (field.placeholder) input.placeholder = field.placeholder;
                if (field.value !== undefined) input.value = field.value;
            }
            input.dataset.fieldName = field.name;
            fieldsBox.appendChild(input);

            if (attachEntityModal.config?.previewFn) {
                input.addEventListener('input', updatePreview);
                input.addEventListener('change', updatePreview);
            }
        });
    }

    function collectFieldValues() {
        const values = {};
        fieldsBox.querySelectorAll('.attach-entity-modal-field').forEach(function (input) {
            values[input.dataset.fieldName] = input.value.trim();
        });
        return values;
    }

    function updatePreview() {
        const config = attachEntityModal.config;
        if (!config?.previewFn) return;
        const values = collectFieldValues();
        const result = config.previewFn(values);

        // previewFn returns one of:
        //   { error: 'message' }
        //     — invalid, BLOCKS saving entirely, no Testen link
        //   { warning: 'message', suggestedTypeId: 'x', preview: 'normalized' }
        //     — valid enough to save, but flags a likely mismatch.
        //       BLOCKS saving until the editor either changes the
        //       type/url (re-running validation fresh) or explicitly
        //       clicks "Trotzdem speichern" to override.
        //   { preview: 'normalized' }
        //     — no concern at all, show preview + Testen link
        //   null/undefined
        //     — nothing typed yet, show nothing
        attachEntityModal._warningAcknowledged = false;

        if (!result) {
            previewBox.style.display = 'none';
            confirmNewBtn.disabled = false;
            return;
        }

        previewBox.style.display = 'block';
        previewBox.classList.toggle('attach-entity-modal-preview--error', !!result.error);
        previewBox.classList.toggle('attach-entity-modal-preview--warning', !!result.warning);

        if (result.error) {
            previewText.innerHTML = '';
            previewText.textContent = result.error;
            testLink.style.display = 'none';
            confirmNewBtn.disabled = true;
            return;
        }

        if (result.warning) {
            confirmNewBtn.disabled = true;
            previewText.innerHTML = '';
            const warningSpan = document.createElement('span');
            warningSpan.textContent = result.warning + ' ';
            previewText.appendChild(warningSpan);

            if (result.suggestedTypeId) {
                const switchLink = document.createElement('a');
                switchLink.href = '#';
                switchLink.className = 'attach-entity-modal-type-switch';
                switchLink.textContent = 'Typ wechseln';
                switchLink.addEventListener('click', function (e) {
                    e.preventDefault();
                    const typeSelect = fieldsBox.querySelector('[data-field-name="url_type_id"]');
                    if (typeSelect) {
                        typeSelect.value = result.suggestedTypeId;
                        updatePreview();
                    }
                });
                previewText.appendChild(switchLink);
            }

            previewText.appendChild(document.createTextNode(' '));

            const overrideLink = document.createElement('a');
            overrideLink.href = '#';
            overrideLink.className = 'attach-entity-modal-type-switch';
            overrideLink.textContent = 'Trotzdem speichern';
            overrideLink.addEventListener('click', function (e) {
                e.preventDefault();
                attachEntityModal._warningAcknowledged = true;
                confirmNewBtn.disabled = false;
            });
            previewText.appendChild(overrideLink);
        } else {
            confirmNewBtn.disabled = false;
            previewText.textContent = 'Wird gespeichert als: ' + result.preview;
        }

        if (result.preview) {
            testLink.href = result.preview;
            testLink.style.display = 'inline-flex';
        } else {
            testLink.style.display = 'none';
        }
    }

    confirmNewBtn?.addEventListener('click', function () {
        const config = attachEntityModal.config;
        if (!config) return;

        const values = collectFieldValues();
        const missingRequired = (config.addFields || []).some(function (f) {
            return f.required && !values[f.name];
        });
        if (missingRequired) return;

        if (config.previewFn) {
            const validation = config.previewFn(values);
            if (validation?.error) return;
            if (validation?.warning && !attachEntityModal._warningAcknowledged) return;
        }

        const data = new FormData();
        Object.entries(values).forEach(function ([key, value]) {
            if (value !== '') data.append(key, value);
        });

        const extra = typeof config.extraAddParams === 'function'
            ? config.extraAddParams()
            : (config.extraAddParams || {});
        Object.entries(extra).forEach(function ([key, value]) {
            data.append(key, value);
        });

        confirmNewBtn.disabled = true;
        fetch(config.addEndpoint, {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(function (json) {
                confirmNewBtn.disabled = false;
                if (!json.success) return;
                if (typeof config.onSelected === 'function') {
                    config.onSelected(json);
                }
                closeAttachEntityModal();
            })
            .catch(function () {
                confirmNewBtn.disabled = false;
            });
    });

    attachEntityModal._renderFields = renderFields;
    attachEntityModal._resetSearch = function () {
        searchInput.value = '';
        resultsBox.innerHTML = '';
    };
    attachEntityModal._resetPreview = function () {
        previewBox.style.display = 'none';
        testLink.style.display = 'none';
        confirmNewBtn.disabled = false;
        attachEntityModal._warningAcknowledged = false;
    };
    attachEntityModal._tabs = tabs;
}

function openAttachEntityModal(config) {
    bindAttachEntityModalOnce();
    if (!attachEntityModal.el) return;

    attachEntityModal.config = config;

    const titleEl = attachEntityModal.el.querySelector('.ows-modal-title');
    if (titleEl) titleEl.textContent = config.title || 'Auswählen';

    const confirmBtn = attachEntityModal.el.querySelector('.ows-modal-btn-primary');
    if (confirmBtn) confirmBtn.textContent = config.confirmLabel || 'Hinzufügen';

    const tabsEl = attachEntityModal.el.querySelector('.attach-entity-modal-tabs');
    const searchInput = attachEntityModal.el.querySelector('.attach-entity-modal-search');

    attachEntityModal._resetSearch();
    attachEntityModal._resetPreview();
    attachEntityModal._renderFields(config.addFields);

    if (config.mode === 'edit') {
        // Edit mode: only the fields form is relevant — no tabs,
        // no "search existing" path, since we're correcting one
        // already-known record, not finding or creating one.
        if (tabsEl) tabsEl.style.display = 'none';
        attachEntityModal.el.querySelectorAll('.attach-entity-modal-panel').forEach(function (panel) {
            panel.style.display = panel.dataset.panel === 'new' ? 'block' : 'none';
        });
        attachEntityModal.el.style.display = 'flex';
        attachEntityModal.el.querySelector('.attach-entity-modal-field')?.focus();
        return;
    }

    if (tabsEl) tabsEl.style.display = 'flex';
    if (searchInput) searchInput.placeholder = config.searchPlaceholder || 'Suchen...';

    attachEntityModal._tabs.forEach(function (t) { t.classList.remove('attach-entity-modal-tab--active'); });
    attachEntityModal._tabs[0]?.classList.add('attach-entity-modal-tab--active');
    attachEntityModal.el.querySelectorAll('.attach-entity-modal-panel').forEach(function (panel) {
        panel.style.display = panel.dataset.panel === 'search' ? 'block' : 'none';
    });

    attachEntityModal.el.style.display = 'flex';
    searchInput?.focus();
}

function closeAttachEntityModal() {
    if (attachEntityModal.el) attachEntityModal.el.style.display = 'none';
    attachEntityModal.config = null;
}