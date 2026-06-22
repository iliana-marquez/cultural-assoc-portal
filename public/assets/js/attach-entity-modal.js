// ── attach_entity_modal ─────────────────────────────────────
// Single, generic owner of #attachEntityModal. Searches existing
// records, creates a new one, or (optionally) picks one of this
// site's own real pages — for whichever entity kind the caller
// configures — no hardcoded knowledge of urls, venues,
// participants, sections, or anything else.
//
// Usage:
//   openAttachEntityModal({
//       title: 'Veranstaltungsort hinzufügen',
//       tabs: ['search', 'new'],  // which tabs to show, in order —
//           omit entirely to default to ['search', 'new']. A
//           caller that has no use for one (e.g. a plain Links
//           entity never needs 'page') simply doesn't list it.
//       searchEndpoint: '/venues/search',
//       addEndpoint: '/venues/add',
//       searchPlaceholder: 'Veranstaltungsort suchen...',
//       addFields: [
//           { name: 'name', label: 'Name', type: 'text', required: true },
//           { name: 'street', label: 'Straße', type: 'text' },
//           ...
//       ],
//       pageFields: [ ... ],  // same shape as addFields — EXTRA
//           fields shown alongside the page-select on the 'page'
//           tab (e.g. a CTA's button-text label). Optional.
//       namedPagesEndpoint: '/urls/named-pages',  // required if
//           'page' tab is requested
//       buildPageUrl: function (path) {
//           return window.location.origin + path;
//       },  // turns the selected page's path into the actual
//           value submitted as 'url' — required if 'page' tab used
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
// types in the add-new (or page) form. Omit entirely for entities
// that don't need this (venues, participants).

const TAB_LABELS = {
    search: 'Vorhandene',
    new: 'Neu hinzufügen',
    page: 'Seite hier'
};

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
    const tabs = attachEntityModal.el.querySelectorAll('.ows-modal-tab');
    const searchInput = attachEntityModal.el.querySelector('.attach-entity-modal-search');
    const resultsBox = attachEntityModal.el.querySelector('.attach-entity-modal-results');
    const fieldsBox = attachEntityModal.el.querySelector('.attach-entity-modal-fields');
    const previewBox = attachEntityModal.el.querySelector('[data-panel="new"] .attach-entity-modal-preview');
    const previewText = previewBox?.querySelector('.attach-entity-modal-preview-text');
    const testLink = previewBox?.querySelector('.attach-entity-modal-test-link');
    const confirmNewBtn = attachEntityModal.el.querySelector('[data-panel="new"] .ows-modal-btn-primary');

    // Page tab's own elements — kept separate from the 'new' panel's,
    // since both panels can be configured independently (different
    // extra fields, different confirm targets), even though they
    // share the same renderFields/collectFieldValues machinery.
    const pageSelect = attachEntityModal.el.querySelector('.attach-entity-modal-page-select');
    const pageFieldsBox = attachEntityModal.el.querySelector('.attach-entity-modal-page-fields');
    const pagePreviewBox = attachEntityModal.el.querySelector('[data-panel="page"] .attach-entity-modal-preview');
    const pagePreviewText = pagePreviewBox?.querySelector('.attach-entity-modal-preview-text');
    const pageTestLink = pagePreviewBox?.querySelector('.attach-entity-modal-test-link');
    const confirmPageBtn = attachEntityModal.el.querySelector('[data-panel="page"] .ows-modal-btn-primary');

    closeBtn?.addEventListener('click', closeAttachEntityModal);
    attachEntityModal.el.addEventListener('click', function (e) {
        if (e.target === attachEntityModal.el) closeAttachEntityModal();
    });

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(function (t) { t.classList.remove('ows-modal-tab--active'); });
            tab.classList.add('ows-modal-tab--active');
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
                            if (config.searchFillsNewTab) {
                                // This caller needs more than just an
                                // attachment (e.g. a CTA's button text) —
                                // searching is a way to PREFILL the
                                // new-record form, not a separate,
                                // immediate-attach path. addForEntity()
                                // already reuses an existing url by its
                                // normalized string match, so routing
                                // back through the normal add flow here
                                // never creates a duplicate row.
                                const urlField = fieldsBox.querySelector('[data-field-name="url"]');
                                const typeField = fieldsBox.querySelector('[data-field-name="url_type_id"]');
                                if (urlField) urlField.value = result.url || '';
                                if (typeField && result.url_type_id !== undefined) {
                                    typeField.value = result.url_type_id;
                                }
                                tabs.forEach(function (t) {
                                    t.classList.toggle('ows-modal-tab--active', t.dataset.tab === 'new');
                                });
                                attachEntityModal.el.querySelectorAll('.attach-entity-modal-panel').forEach(function (panel) {
                                    panel.style.display = panel.dataset.panel === 'new' ? 'block' : 'none';
                                });
                                updatePreview('new');
                                fieldsBox.querySelector('[data-field-name="cta_label"]')?.focus();
                                return;
                            }
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

    function renderFields(container, fields, previewTarget) {
        container.innerHTML = '';
        (fields || []).forEach(function (field) {
            const label = document.createElement('label');
            label.className = 'attach-entity-modal-label';
            label.textContent = field.label + (field.required ? '' : ' (optional)');
            container.appendChild(label);

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
            container.appendChild(input);

            if (attachEntityModal.config?.previewFn) {
                input.addEventListener('input', function () { updatePreview(previewTarget); });
                input.addEventListener('change', function () { updatePreview(previewTarget); });
            }
        });
    }

    function collectFieldValues(container) {
        const values = {};
        container.querySelectorAll('.attach-entity-modal-field').forEach(function (input) {
            values[input.dataset.fieldName] = input.value.trim();
        });
        return values;
    }

    function updatePreview(target) {
        target = target || 'new';
        const config = attachEntityModal.config;

        const isPage = target === 'page';
        const container = isPage ? pageFieldsBox : fieldsBox;
        const pBox = isPage ? pagePreviewBox : previewBox;
        const pText = isPage ? pagePreviewText : previewText;
        const pTestLink = isPage ? pageTestLink : testLink;
        const cBtn = isPage ? confirmPageBtn : confirmNewBtn;

        if (isPage) {
            // A page selected from the dropdown is already guaranteed
            // correct by construction (it came from Router::getLinkablePages()),
            // so there's nothing to VALIDATE here — only something to
            // CONFIRM. Running the same domain-shape check meant for
            // free-typed external URLs would be wrong: it can fail on
            // legitimate local/staging origins (e.g. localhost) that
            // simply don't look like a "real" public domain.
            const selected = pageSelect?.value;
            if (!selected) {
                if (pBox) pBox.style.display = 'none';
                if (cBtn) cBtn.disabled = false;
                return;
            }
            const fullUrl = typeof config.buildPageUrl === 'function'
                ? config.buildPageUrl(selected)
                : selected;
            if (pBox) {
                pBox.style.display = 'block';
                pBox.classList.remove('attach-entity-modal-preview--error', 'attach-entity-modal-preview--warning');
            }
            if (pText) pText.textContent = 'Wird gespeichert als: ' + fullUrl;
            if (pTestLink) {
                pTestLink.href = fullUrl;
                pTestLink.style.display = 'inline-flex';
            }
            if (cBtn) cBtn.disabled = false;
            return;
        }

        if (!config?.previewFn) return;

        const values = collectFieldValues(container);
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
            if (pBox) pBox.style.display = 'none';
            if (cBtn) cBtn.disabled = false;
            return;
        }

        if (!pBox) return;
        pBox.style.display = 'block';
        pBox.classList.toggle('attach-entity-modal-preview--error', !!result.error);
        pBox.classList.toggle('attach-entity-modal-preview--warning', !!result.warning);

        if (result.error) {
            pText.innerHTML = '';
            pText.textContent = result.error;
            if (pTestLink) pTestLink.style.display = 'none';
            if (cBtn) cBtn.disabled = true;
            return;
        }

        if (result.warning) {
            if (cBtn) cBtn.disabled = true;
            pText.innerHTML = '';
            const warningSpan = document.createElement('span');
            warningSpan.textContent = result.warning + ' ';
            pText.appendChild(warningSpan);

            if (result.suggestedTypeId) {
                const switchLink = document.createElement('a');
                switchLink.href = '#';
                switchLink.className = 'attach-entity-modal-type-switch';
                switchLink.textContent = 'Typ wechseln';
                switchLink.addEventListener('click', function (e) {
                    e.preventDefault();
                    const typeSelect = container.querySelector('[data-field-name="url_type_id"]');
                    if (typeSelect) {
                        typeSelect.value = result.suggestedTypeId;
                        updatePreview(target);
                    }
                });
                pText.appendChild(switchLink);
            }

            pText.appendChild(document.createTextNode(' '));

            const overrideLink = document.createElement('a');
            overrideLink.href = '#';
            overrideLink.className = 'attach-entity-modal-type-switch';
            overrideLink.textContent = 'Trotzdem speichern';
            overrideLink.addEventListener('click', function (e) {
                e.preventDefault();
                attachEntityModal._warningAcknowledged = true;
                if (cBtn) cBtn.disabled = false;
            });
            pText.appendChild(overrideLink);
        } else {
            if (cBtn) cBtn.disabled = false;
            pText.textContent = 'Wird gespeichert als: ' + result.preview;
        }

        if (result.preview && pTestLink) {
            pTestLink.href = result.preview;
            pTestLink.style.display = 'inline-flex';
        } else if (pTestLink) {
            pTestLink.style.display = 'none';
        }
    }

    confirmNewBtn?.addEventListener('click', function () {
        const config = attachEntityModal.config;
        if (!config) return;

        const values = collectFieldValues(fieldsBox);
        const missingRequired = (config.addFields || []).some(function (f) {
            return f.required && !values[f.name];
        });
        if (missingRequired) {
            if (previewBox && previewText) {
                previewBox.style.display = 'block';
                previewBox.classList.add('attach-entity-modal-preview--error');
                previewText.textContent = 'Bitte alle erforderlichen Felder ausfüllen.';
                if (testLink) testLink.style.display = 'none';
            }
            return;
        }

        if (config.previewFn) {
            const validation = config.previewFn(values);
            if (validation?.error) return;
            if (validation?.warning && !attachEntityModal._warningAcknowledged) return;
        }

        submitAdd(values, confirmNewBtn, previewBox, previewText);
    });

    confirmPageBtn?.addEventListener('click', function () {
        const config = attachEntityModal.config;
        if (!config || !pageSelect) return;

        const selectedPath = pageSelect.value;
        if (!selectedPath) return;

        const values = collectFieldValues(pageFieldsBox);
        values.url = typeof config.buildPageUrl === 'function'
            ? config.buildPageUrl(selectedPath)
            : selectedPath;
        const missingRequired = (config.pageFields || []).some(function (f) {
            return f.required && !values[f.name];
        });
        if (missingRequired) {
            if (pagePreviewBox && pagePreviewText) {
                pagePreviewBox.style.display = 'block';
                pagePreviewBox.classList.add('attach-entity-modal-preview--error');
                pagePreviewText.textContent = 'Bitte alle erforderlichen Felder ausfüllen.';
                if (pageTestLink) pageTestLink.style.display = 'none';
            }
            return;
        }

        const endpoint = config.mode === 'edit' ? config.addEndpoint : config.pageAddEndpoint;
        submitAdd(values, confirmPageBtn, pagePreviewBox, pagePreviewText, endpoint);
    });

    // Shared by both the 'new' and 'page' panels — same POST target,
    // same extraAddParams merging, same success/error handling.
    // Only the SOURCE of `values` differs between the two callers.
    function submitAdd(values, btn, errBox, errText, endpoint) {
        const config = attachEntityModal.config;

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

        btn.disabled = true;
        fetch(endpoint || config.addEndpoint, {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(function (json) {
                btn.disabled = false;
                if (!json.success) {
                    if (errBox && errText) {
                        errBox.style.display = 'block';
                        errBox.classList.add('attach-entity-modal-preview--error');
                        errText.textContent = json.error || 'Fehler beim Speichern.';
                    }
                    return;
                }
                if (typeof config.onSelected === 'function') {
                    config.onSelected(json);
                }
                closeAttachEntityModal();
            })
            .catch(function () {
                btn.disabled = false;
                if (errBox && errText) {
                    errBox.style.display = 'block';
                    errBox.classList.add('attach-entity-modal-preview--error');
                    errText.textContent = 'Verbindungsfehler.';
                }
            });
    }

    pageSelect?.addEventListener('change', function () {
        updatePreview('page');
    });

    attachEntityModal._renderFields = renderFields;
    attachEntityModal._fieldsBox = fieldsBox;
    attachEntityModal._pageFieldsBox = pageFieldsBox;
    attachEntityModal._pageSelect = pageSelect;
    attachEntityModal._resetSearch = function () {
        searchInput.value = '';
        resultsBox.innerHTML = '';
    };
    attachEntityModal._resetPreview = function () {
        if (previewBox) previewBox.style.display = 'none';
        if (testLink) testLink.style.display = 'none';
        if (confirmNewBtn) confirmNewBtn.disabled = false;
        if (pagePreviewBox) pagePreviewBox.style.display = 'none';
        if (pageTestLink) pageTestLink.style.display = 'none';
        if (confirmPageBtn) confirmPageBtn.disabled = false;
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

    attachEntityModal.el.querySelectorAll('.ows-modal-btn-primary').forEach(function (btn) {
        btn.textContent = config.confirmLabel || 'Hinzufügen';
    });

    const tabsEl = attachEntityModal.el.querySelector('.ows-modal-tabs');
    const searchInput = attachEntityModal.el.querySelector('.attach-entity-modal-search');

    attachEntityModal._resetSearch();
    attachEntityModal._resetPreview();
    attachEntityModal._renderFields(attachEntityModal._fieldsBox, config.addFields, 'new');
    attachEntityModal._renderFields(attachEntityModal._pageFieldsBox, config.pageFields, 'page');

    if (config.mode === 'edit') {
        // Edit mode: only ONE fields form is relevant — no tabs,
        // no "search existing" path, since we're correcting one
        // already-known record, not finding or creating one.
        // Which panel depends on config.editPanel ('new' by default,
        // or 'page' when the value being edited was originally an
        // internal-page link — lets the editor RESELECT a different
        // page from the dropdown, rather than hand-editing a raw URL
        // string to switch destinations).
        const editPanel = config.editPanel || 'new';
        if (tabsEl) tabsEl.style.display = 'none';
        attachEntityModal.el.querySelectorAll('.attach-entity-modal-panel').forEach(function (panel) {
            panel.style.display = panel.dataset.panel === editPanel ? 'block' : 'none';
        });

        if (editPanel === 'page' && attachEntityModal._pageSelect && config.namedPagesEndpoint) {
            const select = attachEntityModal._pageSelect;
            select.innerHTML = '<option value="">— Seite auswählen —</option>';
            fetch(config.namedPagesEndpoint, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.json())
                .then(function (json) {
                    if (!json.success) return;
                    Object.entries(json.pages).forEach(function ([path, label]) {
                        const opt = document.createElement('option');
                        opt.value = path;
                        opt.textContent = label;
                        select.appendChild(opt);
                    });
                    // Pre-select whichever page matches the CTA's
                    // CURRENT destination, so the editor sees exactly
                    // where it points today, not a blank dropdown.
                    if (config.currentPagePath) select.value = config.currentPagePath;
                });
        }

        attachEntityModal.el.style.display = 'flex';
        attachEntityModal.el.querySelector(
            editPanel === 'page' ? '.attach-entity-modal-page-select' : '.attach-entity-modal-field'
        )?.focus();
        return;
    }

    // Which tabs to show, and in what order — driven entirely by
    // config, defaulting to the original two for every caller that
    // doesn't ask for anything else.
    const requestedTabs = config.tabs || ['search', 'new'];
    attachEntityModal._tabs.forEach(function (tab) {
        const show = requestedTabs.includes(tab.dataset.tab);
        tab.style.display = show ? 'inline-flex' : 'none';
        tab.textContent = TAB_LABELS[tab.dataset.tab] || tab.textContent;
    });

    if (tabsEl) tabsEl.style.display = requestedTabs.length > 1 ? 'flex' : 'none';
    if (searchInput) searchInput.placeholder = config.searchPlaceholder || 'Suchen...';

    const firstTab = requestedTabs[0];
    attachEntityModal._tabs.forEach(function (t) {
        t.classList.toggle('ows-modal-tab--active', t.dataset.tab === firstTab);
    });
    attachEntityModal.el.querySelectorAll('.attach-entity-modal-panel').forEach(function (panel) {
        panel.style.display = panel.dataset.panel === firstTab ? 'block' : 'none';
    });

    // Populate the page-select fresh on every open — this list can
    // only ever grow as new pages are added, so there's no benefit
    // to caching it across opens.
    if (requestedTabs.includes('page') && attachEntityModal._pageSelect && config.namedPagesEndpoint) {
        const select = attachEntityModal._pageSelect;
        select.innerHTML = '<option value="">— Seite auswählen —</option>';
        fetch(config.namedPagesEndpoint, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(function (json) {
                if (!json.success) return;
                Object.entries(json.pages).forEach(function ([path, label]) {
                    const opt = document.createElement('option');
                    opt.value = path;
                    opt.textContent = label;
                    select.appendChild(opt);
                });
            });
    }

    attachEntityModal.el.style.display = 'flex';
    if (firstTab === 'search') {
        searchInput?.focus();
    }
}

function closeAttachEntityModal() {
    if (attachEntityModal.el) attachEntityModal.el.style.display = 'none';
    attachEntityModal.config = null;
}