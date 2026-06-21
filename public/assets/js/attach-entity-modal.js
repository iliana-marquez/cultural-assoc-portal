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
            } else {
                input = document.createElement('input');
                input.type = field.type || 'text';
                input.className = 'ows-modal-text-input attach-entity-modal-field';
                if (field.placeholder) input.placeholder = field.placeholder;
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
        const preview = config.previewFn(values);
        if (!preview) {
            previewBox.style.display = 'none';
            return;
        }
        previewBox.style.display = 'block';
        previewText.textContent = 'Wird gespeichert als: ' + preview;
        testLink.href = preview;
        testLink.style.display = 'inline-flex';
    }

    confirmNewBtn?.addEventListener('click', function () {
        const config = attachEntityModal.config;
        if (!config) return;

        const values = collectFieldValues();
        const missingRequired = (config.addFields || []).some(function (f) {
            return f.required && !values[f.name];
        });
        if (missingRequired) return;

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
    };
    attachEntityModal._tabs = tabs;
}

function openAttachEntityModal(config) {
    bindAttachEntityModalOnce();
    if (!attachEntityModal.el) return;

    attachEntityModal.config = config;

    const titleEl = attachEntityModal.el.querySelector('.ows-modal-title');
    if (titleEl) titleEl.textContent = config.title || 'Auswählen';

    const searchInput = attachEntityModal.el.querySelector('.attach-entity-modal-search');
    if (searchInput) searchInput.placeholder = config.searchPlaceholder || 'Suchen...';

    attachEntityModal._resetSearch();
    attachEntityModal._resetPreview();
    attachEntityModal._renderFields(config.addFields);

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