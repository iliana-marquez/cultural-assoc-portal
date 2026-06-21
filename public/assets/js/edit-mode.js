/**
 * edit-mode.js
 * OWS — Inline edit mode for sections and entity records.
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── State ─────────────────────────────────────────────────
    let activeBlock = null;
    let hasUnsaved = false;
    let originalValues = {};

    // ──────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────

    function showBlockFeedback(block, message, type) {
        let fb = block.querySelector('.block-feedback');
        if (!fb) return;
        fb.textContent = message;
        fb.className = 'block-feedback block-feedback--' + type;
        setTimeout(function () { fb.textContent = ''; }, 3000);
    }

    function showEntityFeedback(row, message, type, options) {
        options = options || {};
        let fb = row.querySelector('.entity-feedback');
        if (!fb) {
            fb = document.createElement('span');
            fb.className = 'entity-feedback';
            row.appendChild(fb);
        }
        // Cancel any previously scheduled auto-clear so it can't fire
        // late and blank out a newer message.
        if (fb._clearTimeout) {
            clearTimeout(fb._clearTimeout);
            fb._clearTimeout = null;
        }
        fb.textContent = message;
        fb.className = 'entity-feedback entity-feedback--' + type;
        // Transient "in progress" messages (e.g. "Wird hochgeladen...")
        // should stay visible until replaced by a real result, not
        // disappear on their own after a fixed delay.
        if (!options.persistent) {
            fb._clearTimeout = setTimeout(function () {
                fb.textContent = '';
                fb._clearTimeout = null;
            }, 3000);
        }
    }

    // ── input_modal ──────────────────────────────────────────
    // Single, generic owner of #inputModal. Anyone needing to
    // collect a piece of text (caption, credit, future free-text
    // fields) calls openInputModal({...}) — only one click listener
    // is ever attached to the Speichern/Abbrechen buttons, no matter
    // how many different callers use this modal over the page's
    // lifetime. Each call simply replaces "what happens on confirm."
    const inputModal = {
        el: document.getElementById('inputModal'),
        title: null,
        area: null,
        cancelBtn: null,
        confirmBtn: null,
        onConfirm: null,
        bound: false
    };

    function bindInputModalOnce() {
        if (inputModal.bound || !inputModal.el) return;
        inputModal.bound = true;
        inputModal.title = inputModal.el.querySelector('.input-modal-title');
        inputModal.area = inputModal.el.querySelector('.input-modal-textarea');
        inputModal.cancelBtn = inputModal.el.querySelector('.input-modal-cancel');
        inputModal.confirmBtn = inputModal.el.querySelector('.input-modal-confirm');
        const closeBtn = inputModal.el.querySelector('.input-modal-close');

        inputModal.cancelBtn?.addEventListener('click', function () {
            closeInputModal();
        });

        closeBtn?.addEventListener('click', function () {
            closeInputModal();
        });

        inputModal.confirmBtn?.addEventListener('click', function () {
            const value = inputModal.area?.value.trim() ?? '';
            if (typeof inputModal.onConfirm === 'function') {
                inputModal.onConfirm(value);
            }
        });

        inputModal.el.addEventListener('click', function (e) {
            if (e.target === inputModal.el) closeInputModal();
        });
    }

    /**
     * Open the shared input modal.
     *
     * @param {object} config
     *   title        string   modal heading
     *   placeholder  string   textarea placeholder
     *   initialValue string   pre-filled textarea value (default '')
     *   onConfirm    function(value) — called with the trimmed
     *                textarea value when Speichern is clicked.
     *                The caller is responsible for closing the modal
     *                (call closeInputModal()) once its own save
     *                succeeds — this keeps the modal open on error
     *                so the user doesn't lose their typed text.
     */
    function openInputModal(config) {
        bindInputModalOnce();
        if (!inputModal.el) return;
        inputModal.onConfirm = config.onConfirm || null;
        if (inputModal.title) inputModal.title.textContent = config.title || '';
        if (inputModal.area) {
            inputModal.area.value = config.initialValue || '';
            inputModal.area.placeholder = config.placeholder || '';
        }
        inputModal.el.style.display = 'flex';
        inputModal.area?.focus();
    }

    function closeInputModal() {
        if (inputModal.el) inputModal.el.style.display = 'none';
        inputModal.onConfirm = null;
    }

    // ──────────────────────────────────────────────────────────
    // FREE SECTIONS — editable-block
    // ──────────────────────────────────────────────────────────

    function activateBlock(block) {
        if (activeBlock && activeBlock !== block && hasUnsaved) {
            if (!confirm('Ungespeicherte Änderungen verwerfen?')) return;
            cancelBlock(activeBlock);
        }
        if (activeBlock && activeBlock !== block) deactivateBlock(activeBlock);

        activeBlock = block;
        block.classList.add('editing');
        document.body.classList.add('is-editing');

        // Show field labels
        block.querySelectorAll('.edit-field-label').forEach(el => {
            el.style.display = 'block';
        });

        originalValues = {};
        block.querySelectorAll('[data-field]').forEach(function (el) {
            originalValues[el.dataset.field] = el.innerHTML;
            el.contentEditable = 'true';
            el.classList.add('editable-field');
        });

        hasUnsaved = false;
        block.querySelectorAll('[data-field]').forEach(function (el) {
            el.addEventListener('input', function () { hasUnsaved = true; });
        });

        // Save current toggle values before clone
        const toggleValues = {};
        block.querySelectorAll('[data-toggle]').forEach(function (el) {
            toggleValues[el.dataset.toggle] = el.dataset.value;
        });

        // Clone toggle controls to clear stale listeners
        const editControls = block.querySelector('.block-edit-controls');
        if (editControls) {
            const fresh = editControls.cloneNode(true);
            editControls.replaceWith(fresh);
        }

        // Restore saved values on fresh clone
        block.querySelectorAll('[data-toggle]').forEach(function (el) {
            if (toggleValues[el.dataset.toggle] !== undefined) {
                el.dataset.value = toggleValues[el.dataset.toggle];
            }
        });

        initToggles(block);
        initImageControls(block);
    }

    function deactivateBlock(block) {
        if (!block.classList.contains('editing')) return;
        block.classList.remove('editing');
        block.querySelectorAll('[data-field]').forEach(function (el) {
            el.contentEditable = 'false';
            el.classList.remove('editable-field');
        });
        const saveBtn = block.querySelector('.btn-save');
        if (saveBtn) {
            saveBtn.disabled = false;
            const icon = saveBtn.querySelector('i');
            if (icon) icon.className = 'ti ti-check';
        }
        // Hide field labels
        block.querySelectorAll('.edit-field-label').forEach(el => {
            el.style.display = 'none';
        });

        document.body.classList.remove('is-editing');
        activeBlock = null;
        hasUnsaved = false;
        originalValues = {};
    }

    function cancelBlock(block) {
        if (!block.classList.contains('editing')) return;
        block.querySelectorAll('[data-field]').forEach(function (el) {
            if (originalValues[el.dataset.field] !== undefined) {
                el.innerHTML = originalValues[el.dataset.field];
            }
        });
        deactivateBlock(block);
    }

    // ── Save ──────────────────────────────────────────────────

    function saveBlock(block) {
        if (!block.classList.contains('editing')) return;
        const saveUrl = block.dataset.saveUrl;
        const data = new FormData();

        block.querySelectorAll('[data-field]').forEach(function (el) {
            data.append(el.dataset.field, el.innerText.trim());
        });
        block.querySelectorAll('[data-toggle]').forEach(function (el) {
            data.append(el.dataset.toggle, el.dataset.value);
        });

        const saveBtn = block.querySelector('.btn-save');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.querySelector('i').className = 'ti ti-loader';
        }

        fetch(saveUrl, {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(function (json) {
                if (json.success) {
                    showBlockFeedback(block, 'Gespeichert ✓', 'success');
                    deactivateBlock(block);
                } else {
                    showBlockFeedback(block, 'Fehler: ' + (json.error ?? ''), 'error');
                    if (saveBtn) { saveBtn.disabled = false; saveBtn.querySelector('i').className = 'ti ti-check'; }
                }
            })
            .catch(function () {
                showBlockFeedback(block, 'Verbindungsfehler', 'error');
                if (saveBtn) { saveBtn.disabled = false; saveBtn.querySelector('i').className = 'ti ti-check'; }
            });
    }

    // ── Toggle controls ───────────────────────────────────────

    function initToggles(block) {

        // Theme
        block.querySelectorAll('[data-action="toggle-theme"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const section = block.querySelector('.segment');
                if (!section) return;
                const isDark = section.classList.contains('dark-segment');
                section.classList.toggle('dark-segment', !isDark);
                section.classList.toggle('light-segment', isDark);
                btn.closest('[data-toggle]').dataset.value = isDark ? 'light' : 'dark';
                hasUnsaved = true;
            });
        });

        // Layout
        block.querySelectorAll('[data-action="toggle-layout"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const layouts = ['50-50', '75-25', '25-75', '100-100'];
                const toggle = btn.closest('[data-toggle]');
                const next = layouts[(layouts.indexOf(toggle.dataset.value) + 1) % layouts.length];
                toggle.dataset.value = next;
                btn.querySelector('.layout-label').textContent = next;
                updateColumns(block, next);
                hasUnsaved = true;
            });
        });

        // Flip
        block.querySelectorAll('[data-action="toggle-flip"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const toggle = btn.closest('[data-toggle]');
                const next = toggle.dataset.value === 'left' ? 'right' : 'left';
                toggle.dataset.value = next;
                flipImage(block, next);
                hasUnsaved = true;
            });
        });

        // Object fit
        block.querySelectorAll('[data-action="toggle-fit"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const toggle = btn.closest('[data-toggle]');
                const next = toggle.dataset.value === 'cover' ? 'contain' : 'cover';
                toggle.dataset.value = next;
                const img = block.querySelector('.section-image-col img');
                if (img) img.style.objectFit = next;
                hasUnsaved = true;
            });
        });

        // Block type toggle — add/remove image column
        block.querySelectorAll('[data-action="toggle-block-type"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const toggle = btn.closest('[data-toggle]');
                const current = toggle.dataset.value;
                const next = current === 'text' ? 'image' : 'text';
                toggle.dataset.value = next;

                btn.querySelector('.block-type-label').textContent =
                    next === 'image' ? 'Bildspalte entfernen' : '+ Bildspalte';
                btn.querySelector('i').className =
                    next === 'image'
                        ? 'ti ti-layout-sidebar-right-collapse'
                        : 'ti ti-layout-sidebar-right';

                block.querySelectorAll('.ctrl-text-block').forEach(el => {
                    el.classList.toggle('d-none', next !== 'text');
                });
                block.querySelectorAll('.ctrl-image-block').forEach(el => {
                    el.classList.toggle('d-none', next !== 'image');
                });

                const imageCol = block.querySelector('.section-image-col');
                const contentCol = block.querySelector('.section-text-col');

                if (next === 'image') {
                    if (imageCol) imageCol.classList.remove('d-none');
                    const layoutToggle = block.querySelector('[data-toggle="layout"]');
                    const layout = layoutToggle?.dataset.value || '50-50';
                    updateColumns(block, layout);
                } else {
                    if (imageCol) imageCol.classList.add('d-none');
                    if (contentCol) {
                        const id = contentCol.id;
                        contentCol.className = 'col-12 section-text-col';
                        if (id) contentCol.id = id;
                    }
                }

                const imagePosToggle = block.querySelector('[data-toggle="image_pos"]');
                if (imagePosToggle) {
                    imagePosToggle.dataset.value = next === 'image' ? 'right' : 'none';
                }

                hasUnsaved = true;
            });
        });

        // Text align
        block.querySelectorAll('[data-action="toggle-align"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const aligns = ['left', 'center', 'right'];
                const toggle = btn.closest('[data-toggle]');
                const next = aligns[(aligns.indexOf(toggle.dataset.value) + 1) % aligns.length];
                toggle.dataset.value = next;
                btn.querySelector('.align-label').textContent = next;
                const content = block.querySelector('.section-content');
                if (content) {
                    content.classList.remove('text-center', 'text-end');
                    if (next === 'center') content.classList.add('text-center');
                    if (next === 'right') content.classList.add('text-end');
                }
                hasUnsaved = true;
            });
        });
    }

    // ── Column helpers ────────────────────────────────────────

    function updateColumns(block, layout) {
        const row = block.querySelector('.row');
        if (!row) return;
        const sizes = {
            '50-50': { text: 'col-12 col-md-6', image: 'col-12 col-md-6' },
            '75-25': { text: 'col-12 col-md-8', image: 'col-12 col-md-4' },
            '25-75': { text: 'col-12 col-md-4', image: 'col-12 col-md-8' },
            '100-100': { text: 'col-12', image: 'col-12' },
        };
        const s = sizes[layout] || sizes['50-50'];
        const textCol = row.querySelector('.section-text-col');
        const imageCol = row.querySelector('.section-image-col');
        const hidden = imageCol?.classList.contains('d-none') ? ' d-none' : '';
        const id = textCol?.id;
        if (textCol) { textCol.className = s.text + ' section-text-col'; if (id) textCol.id = id; }
        if (imageCol) { imageCol.className = s.image + ' section-image-col' + hidden; }
    }

    function flipImage(block, position) {
        const row = block.querySelector('.row');
        const imageCol = row?.querySelector('.section-image-col');
        const contentCol = row?.querySelector('.section-text-col');
        if (!row || !imageCol || !contentCol) return;
        if (position === 'left') {
            row.insertBefore(imageCol, contentCol);
        } else {
            row.insertBefore(contentCol, imageCol);
        }
    }

    // ── Image controls ────────────────────────────────────────

    function initImageControls(block) {
        const sectionId = block.dataset.sectionId;

        block.querySelectorAll('[data-action="upload-image"]').forEach(function (input) {
            input.addEventListener('change', function () {
                if (!this.files[0]) return;
                uploadSectionImage(block, this.files[0], 'image', sectionId);
            });
        });

        block.querySelectorAll('[data-action="remove-image"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!confirm('Bild entfernen?')) return;
                removeSectionImage(block, 'image', sectionId);
            });
        });

        block.querySelector('[data-action="upload-bg"]')?.addEventListener('change', function () {
            if (!this.files[0]) return;
            uploadSectionImage(block, this.files[0], 'bg_image', sectionId);
        });

        block.querySelector('[data-action="remove-bg"]')?.addEventListener('click', function () {
            if (!confirm('Hintergrundbild entfernen?')) return;
            removeSectionImage(block, 'bg_image', sectionId);
        });
    }

    function uploadSectionImage(block, file, field, sectionId) {
        const data = new FormData();
        data.append('image', file);
        data.append('entity_type', 'section');
        data.append('entity_id', sectionId);
        data.append('field', field);
        data.append('subfolder', 'pages');

        showBlockFeedback(block, 'Wird hochgeladen...', 'success');

        fetch('/media/upload-section', {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(function (json) {
                if (json.success) {
                    if (field === 'image') {
                        const imageCol = block.querySelector('.section-image-col');
                        const imgContainer = imageCol?.querySelector('.img-placeholder');
                        const existingImg = imgContainer?.querySelector('img');

                        if (imgContainer && !existingImg) {
                            imgContainer.innerHTML =
                                '<img src="' + json.url + '">' +
                                '<div class="image-edit-overlay">' +
                                '<label class="section-control-btn" style="cursor:pointer;">' +
                                '<i class="ti ti-refresh"></i> Ändern' +
                                '<input type="file" accept="image/*" class="d-none" data-action="upload-image">' +
                                '</label>' +
                                '<button class="section-control-btn" data-action="remove-image">' +
                                '<i class="ti ti-trash"></i> Entfernen' +
                                '</button>' +
                                '</div>';
                            initImageControls(block);
                        } else if (existingImg) {
                            existingImg.src = json.url;
                        }

                        if (imageCol) imageCol.classList.remove('d-none');

                        const layoutToggle = block.querySelector('[data-toggle="layout"]');
                        const layout = layoutToggle?.dataset.value || '50-50';
                        updateColumns(block, layout);

                    } else {
                        const segment = block.querySelector('.segment');
                        segment.style.backgroundImage = 'url(' + json.url + ')';
                        if (!block.querySelector('.segment-overlay')) {
                            const overlay = document.createElement('div');
                            overlay.className = 'segment-overlay';
                            segment.prepend(overlay);
                        }
                        // Swap + BG → BG entfernen
                        const bgWrap = block.querySelector('.bg-btn-wrap');
                        if (bgWrap) {
                            bgWrap.innerHTML =
                                '<button class="section-control-btn" data-action="remove-bg">' +
                                '<i class="ti ti-wallpaper-off"></i> BG entfernen' +
                                '</button>';
                            initImageControls(block);
                        }
                    }
                    hasUnsaved = true;
                    showBlockFeedback(block, 'Bild hochgeladen ✓', 'success');
                } else {
                    showBlockFeedback(block, 'Upload fehlgeschlagen', 'error');
                }
            })
            .catch(function () {
                showBlockFeedback(block, 'Verbindungsfehler', 'error');
            });
    }

    function removeSectionImage(block, field, sectionId) {
        const data = new FormData();
        data.append('field', field);
        data.append('section_id', sectionId);

        fetch('/page/section/' + sectionId + '/remove-image', {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(function (json) {
                if (json.success) {
                    if (field === 'image') {
                        const imageCol = block.querySelector('.section-image-col');
                        const imgContainer = imageCol?.querySelector('.img-placeholder');
                        if (imgContainer) {
                            imgContainer.innerHTML =
                                '<i class="ti ti-photo"></i>' +
                                '<label class="section-control-btn placeholder-upload-btn" style="cursor:pointer;">' +
                                '<i class="ti ti-photo-plus"></i> Bild hinzufügen' +
                                '<input type="file" accept="image/*" class="d-none" data-action="upload-image">' +
                                '</label>';
                            initImageControls(block);
                        }
                    } else {
                        const segment = block.querySelector('.segment');
                        segment.style.backgroundImage = '';
                        block.querySelector('.segment-overlay')?.remove();
                        // Swap BG entfernen → + BG
                        const bgWrap = block.querySelector('.bg-btn-wrap');
                        if (bgWrap) {
                            bgWrap.innerHTML =
                                '<label class="section-control-btn" style="cursor:pointer;">' +
                                '<i class="ti ti-wallpaper"></i> BG' +
                                '<input type="file" accept="image/*" class="d-none" data-action="upload-bg">' +
                                '</label>';
                            initImageControls(block);
                        }
                    }
                    hasUnsaved = true;
                    showBlockFeedback(block, 'Entfernt ✓', 'success');
                } else {
                    showBlockFeedback(block, 'Fehler', 'error');
                }
            });
    }

    // ── Init blocks — attach save/cancel ONCE ─────────────────

    document.querySelectorAll('.editable-block').forEach(function (block) {

        block.querySelector('.btn-edit')?.addEventListener('click', function (e) {
            e.stopPropagation();
            if (!block.classList.contains('editing')) activateBlock(block);
        });

        block.querySelector('.btn-save')?.addEventListener('click', function (e) {
            e.stopPropagation();
            saveBlock(block);
        });

        block.querySelector('.btn-cancel')?.addEventListener('click', function (e) {
            e.stopPropagation();
            if (hasUnsaved && !confirm('Änderungen verwerfen?')) return;
            cancelBlock(block);
        });
    });

    // ──────────────────────────────────────────────────────────
    // ENTITY EDIT ROWS
    // ──────────────────────────────────────────────────────────

    document.querySelectorAll('.entity-edit-row').forEach(function (row) {
        const field = row.querySelector('.entity-field');
        const editBtn = row.querySelector('.entity-edit-btn');
        const saveBtn = row.querySelector('.entity-save-btn');
        const cancelBtn = row.querySelector('.entity-cancel-btn');
        const saveUrl = row.dataset.saveUrl;
        const fieldName = field?.dataset.field;
        let original = field?.innerText ?? '';

        // Hide save/cancel on init
        if (saveBtn) saveBtn.style.display = 'none';
        if (cancelBtn) cancelBtn.style.display = 'none';

        editBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            original = field.innerText;
            field.contentEditable = 'true';
            field.focus();
            row.classList.add('editing');
            editBtn.style.display = 'none';
            saveBtn.style.display = 'inline-flex';
            cancelBtn.style.display = 'inline-flex';
            document.body.classList.add('is-editing');
        });

        cancelBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            field.innerText = original;
            field.contentEditable = 'false';
            row.classList.remove('editing');
            editBtn.style.display = 'inline-flex';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
            document.body.classList.remove('is-editing');
        });

        saveBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            const value = field.innerText.trim();
            const data = new FormData();
            data.append(fieldName, value);
            saveBtn.disabled = true;

            fetch(saveUrl, {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.json())
                .then(function (json) {
                    if (json.success) {
                        field.contentEditable = 'false';
                        row.classList.remove('editing');
                        editBtn.style.display = 'inline-flex';
                        saveBtn.style.display = 'none';
                        cancelBtn.style.display = 'none';
                        document.body.classList.remove('is-editing');
                        showEntityFeedback(row, 'Gespeichert ✓', 'success');
                        if (json.slug) {
                            history.replaceState(null, '', '/veranstaltungen/' + json.slug);
                        }
                    } else {
                        showEntityFeedback(row, 'Fehler', 'error');
                    }
                    saveBtn.disabled = false;
                })
                .catch(function () {
                    showEntityFeedback(row, 'Verbindungsfehler', 'error');
                    saveBtn.disabled = false;
                });
        });
    });

    // ── Entity select rows (venue, admission) ────────────────
    document.querySelectorAll('.entity-select-row').forEach(function (row) {
        const select = row.querySelector('select');
        const editBtn = row.querySelector('.entity-edit-btn');
        const saveBtn = row.querySelector('.entity-save-btn');
        const cancelBtn = row.querySelector('.entity-cancel-btn');
        const saveUrl = row.dataset.saveUrl;
        const fieldName = select?.dataset.field;
        const display = row.querySelector('.entity-select-display');
        let original = select?.value ?? '';

        if (saveBtn) saveBtn.style.display = 'none';
        if (cancelBtn) cancelBtn.style.display = 'none';

        editBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            original = select.value;
            row.classList.add('editing');
            editBtn.style.display = 'none';
            saveBtn.style.display = 'inline-flex';
            cancelBtn.style.display = 'inline-flex';
            document.body.classList.add('is-editing');
        });

        cancelBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            select.value = original;
            row.classList.remove('editing');
            editBtn.style.display = 'inline-flex';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
            document.body.classList.remove('is-editing');
        });

        saveBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            const data = new FormData();
            data.append(fieldName, select.value);
            saveBtn.disabled = true;
            fetch(saveUrl, {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.json())
                .then(function (json) {
                    if (json.success) {
                        if (display) display.textContent = select.options[select.selectedIndex]?.text ?? '—';
                        row.classList.remove('editing');
                        editBtn.style.display = 'inline-flex';
                        saveBtn.style.display = 'none';
                        cancelBtn.style.display = 'none';
                        document.body.classList.remove('is-editing');
                        showEntityFeedback(row, 'Gespeichert ✓', 'success');
                    } else {
                        showEntityFeedback(row, 'Fehler', 'error');
                    }
                    saveBtn.disabled = false;
                })
                .catch(function () {
                    showEntityFeedback(row, 'Verbindungsfehler', 'error');
                    saveBtn.disabled = false;
                });
        });
    });

    // ── Participants edit row ─────────────────────────────────
    document.querySelectorAll('.participants-edit-row').forEach(function (row) {
        const editBtn = row.querySelector('.entity-edit-btn');
        const saveBtn = row.querySelector('.entity-save-btn');
        const cancelBtn = row.querySelector('.entity-cancel-btn');
        const addBtn = row.querySelector('[data-action="add-participant"]');
        const addSelect = row.querySelector('.entity-select');

        if (saveBtn) saveBtn.style.display = 'none';
        if (cancelBtn) cancelBtn.style.display = 'none';

        editBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            row.classList.add('editing');
            editBtn.style.display = 'none';
            saveBtn.style.display = 'inline-flex';
            cancelBtn.style.display = 'inline-flex';
            document.body.classList.add('is-editing');
        });

        cancelBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            row.classList.remove('editing');
            editBtn.style.display = 'inline-flex';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
            document.body.classList.remove('is-editing');
        });

        saveBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            row.classList.remove('editing');
            editBtn.style.display = 'inline-flex';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
            document.body.classList.remove('is-editing');
        });

        addBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            const participantId = addSelect?.value;
            const eventId = addBtn.dataset.eventId;
            if (!participantId) return;
            const selectedText = addSelect.options[addSelect.selectedIndex]?.text.trim() ?? '';

            addBtn.disabled = true;
            const data = new FormData();
            data.append('participant_id', participantId);
            fetch('/events/' + eventId + '/participant/add', {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.json())
                .then(function (json) {
                    addBtn.disabled = false;
                    if (json.success) {
                        let list = row.querySelector('.event-participant-list');
                        if (!list) {
                            list = document.createElement('div');
                            list.className = 'event-participant-list p-2';
                            row.querySelector('.add-participant-wrap')?.insertAdjacentElement('beforebegin', list);
                        }
                        const item = document.createElement('div');
                        item.className = 'event-participant-item';
                        item.innerHTML =
                            '<button class="entity-remove-btn border-0" data-action="remove-participant"' +
                            ' data-event-id="' + eventId + '" data-participant-id="' + participantId + '">' +
                            '<i class="ti ti-trash"></i></button>' +
                            '<a href="#">' + selectedText + '</a>';
                        list.appendChild(item);
                        list.querySelector('.text-muted')?.remove();
                        bindRemoveParticipant(item.querySelector('[data-action="remove-participant"]'), row);
                        addSelect.value = '';
                        showEntityFeedback(row, 'Hinzugefügt ✓', 'success');
                    } else {
                        showEntityFeedback(row, 'Fehler', 'error');
                    }
                })
                .catch(function () {
                    addBtn.disabled = false;
                    showEntityFeedback(row, 'Verbindungsfehler', 'error');
                });
        });

        function bindRemoveParticipant(btn, row) {
            btn?.addEventListener('click', function (e) {
                e.stopPropagation();
                if (!confirm('Mitwirkende:n entfernen?')) return;
                const data = new FormData();
                data.append('participant_id', btn.dataset.participantId);
                fetch('/events/' + btn.dataset.eventId + '/participant/remove', {
                    method: 'POST',
                    body: data,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        if (json.success) {
                            const list = btn.closest('.event-participant-list');
                            btn.closest('.event-participant-item')?.remove();
                            if (list && list.querySelectorAll('.event-participant-item').length === 0) {
                                list.innerHTML =
                                    '<p class="text-muted p-2 mb-0">' +
                                    '<i class="ti ti-users-group"></i> ' +
                                    'Noch keine Mitwirkenden' +
                                    '</p>';
                            }
                            showEntityFeedback(row, 'Entfernt ✓', 'success');
                        } else {
                            showEntityFeedback(row, 'Fehler', 'error');
                        }
                    })
                    .catch(function () { showEntityFeedback(row, 'Verbindungsfehler', 'error'); });
            });
        }

        row.querySelectorAll('[data-action="remove-participant"]').forEach(function (btn) {
            bindRemoveParticipant(btn, row);
        });
    });

    // ── Links (entity_urls) ────────────────────────────────────
    document.querySelectorAll('.links-edit-row').forEach(function (row) {
        const entityType = row.dataset.entityType;
        const entityId = row.dataset.entityId;

        const linksPencilBtn = row.querySelector('.links-pencil-btn');
        const linksCancelBtn = row.querySelector('.links-cancel-btn');

        linksPencilBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            row.classList.add('editing');
            document.body.classList.add('is-editing');
        });

        linksCancelBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            row.classList.remove('editing');
            document.body.classList.remove('is-editing');
        });

        // Shared validation helpers — used by both the "add new"
        // and "edit existing" flows, so this only needs to exist once.
        const platformDomains = {
            facebook: ['facebook.com'],
            instagram: ['instagram.com'],
            linkedin: ['linkedin.com'],
            youtube: ['youtube.com', 'youtu.be'],
            spotify: ['spotify.com'],
            soundcloud: ['soundcloud.com'],
            vimeo: ['vimeo.com'],
            bandcamp: ['bandcamp.com']
        };

        function hostMatchesDomain(host, domain) {
            return host === domain || host.endsWith('.' + domain);
        }

        function isValidMapsUrl(host, pathname) {
            if (hostMatchesDomain(host, 'maps.google.com')) return true;
            if (hostMatchesDomain(host, 'maps.apple.com')) return true;
            if (hostMatchesDomain(host, 'openstreetmap.org')) return true;
            if (hostMatchesDomain(host, 'goo.gl')) return true;
            // Bare google.com is too broad to accept on domain alone
            // (it's also search, gmail, etc.) — require /maps in the path.
            if (hostMatchesDomain(host, 'google.com') && pathname.startsWith('/maps')) return true;
            return false;
        }

        function detectPlatformMatch(host) {
            for (const key in platformDomains) {
                if (platformDomains[key].some(function (d) { return hostMatchesDomain(host, d); })) {
                    return key;
                }
            }
            return null;
        }

        function validateAndPreview(values, typeOptions) {
            const typeId = values.url_type_id;
            const rawUrl = (values.url || '').trim();
            if (!rawUrl) return null;

            const typeOpt = typeOptions.find(function (t) { return String(t.value) === String(typeId); });
            const typeLabel = (typeOpt?.label || '').toLowerCase();

            if (typeLabel === 'email') {
                const candidate = rawUrl.replace(/^mailto:/i, '');
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(candidate)) {
                    return { error: 'Bitte eine gültige E-Mail-Adresse eingeben.' };
                }
                return { preview: 'mailto:' + candidate.toLowerCase() };
            }

            let normalized = rawUrl.replace(/^http:\/\//i, 'https://');
            if (!/^https?:\/\//i.test(normalized)) normalized = 'https://' + normalized;
            normalized = normalized.replace(/\/$/, '');

            let host, pathname;
            try {
                const parsed = new URL(normalized);
                host = parsed.hostname.toLowerCase();
                pathname = parsed.pathname;
            } catch (err) {
                return { error: 'Bitte eine gültige URL eingeben.' };
            }

            // new URL() is permissive — it parses almost anything into
            // SOME hostname, even pasted garbage text. This stricter
            // check requires a real domain.tld shape (labels separated
            // by dots, ending in an alphabetic top-level domain), which
            // is the actual gap that let nonsense slip through before.
            const domainShapePattern = /^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/;
            if (!domainShapePattern.test(host)) {
                return { error: 'Bitte eine gültige URL eingeben (z. B. example.com).' };
            }

            if (typeLabel === 'maps') {
                if (!isValidMapsUrl(host, pathname)) {
                    return { error: 'Diese URL scheint kein Karten-Link zu sein (z. B. Google Maps, Apple Maps, OpenStreetMap).' };
                }
                return { preview: normalized };
            }

            const requiredDomains = platformDomains[typeLabel];
            if (requiredDomains) {
                const matches = requiredDomains.some(function (domain) {
                    return hostMatchesDomain(host, domain);
                });
                if (!matches) {
                    return { error: 'Diese URL scheint nicht zu ' + (typeOpt?.label || 'diesem Typ') + ' zu gehören (erwartet: ' + requiredDomains.join(' oder ') + ').' };
                }
                return { preview: normalized };
            }

            // Soft check — only for genuinely unclaimed types (Website,
            // Other, Press, Radio, TV), since they make no domain claim
            // of their own. Warn, don't block, if the domain matches a
            // known specific platform. Maps is deliberately excluded —
            // it already has its own strict rule above, so there's no
            // "soft" case for it.
            const unclaimedTypes = ['website', 'other', 'press', 'radio', 'tv'];
            if (unclaimedTypes.includes(typeLabel)) {
                const matchedPlatform = detectPlatformMatch(host);
                if (matchedPlatform) {
                    const matchedOpt = typeOptions.find(function (t) {
                        return (t.label || '').toLowerCase() === matchedPlatform;
                    });
                    const platformName = matchedOpt?.label || matchedPlatform;
                    return {
                        warning: 'Dieser Link sieht nach ' + platformName + ' aus. Ändere den Typ zu ' + platformName + ', oder überprüfe die URL, falls das nicht stimmt.',
                        suggestedTypeId: matchedOpt?.value,
                        preview: normalized
                    };
                }
            }

            return { preview: normalized };
        }

        const addBtn = row.querySelector('[data-action="add-entity-url"]');
        addBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            fetch('/urls/types', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.json())
                .then(function (json) {
                    const typeOptions = (json.success ? json.types : []).map(function (t) {
                        return { value: t.id, label: t.label };
                    });

                    openAttachEntityModal({
                        title: 'Link hinzufügen',
                        searchEndpoint: '/urls/search',
                        addEndpoint: '/urls/add',
                        searchPlaceholder: 'Vorhandene Links durchsuchen...',
                        addFields: [
                            { name: 'url_type_id', label: 'Typ', type: 'select', required: true, options: typeOptions },
                            { name: 'url', label: 'URL', type: 'text', required: true, placeholder: 'https://...' },
                            { name: 'label', label: 'Bezeichnung', type: 'text' }
                        ],
                        previewFn: function (values) { return validateAndPreview(values, typeOptions); },
                        extraAddParams: { entity_type: entityType, entity_id: entityId },
                        renderResultItem: function (result) {
                            return '<i class="ti ' + (result.icon || 'ti-link') + '"></i> ' +
                                (result.label || result.url);
                        },
                        onSelected: function (result) {
                            // The add-new path (UrlController::add) already
                            // attaches to this entity server-side, in the same
                            // request — its response includes "success". The
                            // pick-existing path (raw search result row) does
                            // NOT include that key, and still needs an explicit
                            // attach call to link it to this entity.
                            if (result.success) {
                                appendUrlToList(row, result);
                                showEntityFeedback(row, 'Hinzugefügt ✓', 'success');
                            } else {
                                attachAndRenderUrl(row, result, entityType, entityId);
                            }
                        }
                    });
                })
                .catch(function () {
                    showEntityFeedback(row, 'Verbindungsfehler', 'error');
                });
        });

        function attachAndRenderUrl(row, result, entityType, entityId) {
            // If this came from search (not add-new), it still needs
            // an explicit attach call. Add-new already attached server-side,
            // but calling attach again is harmless (INSERT IGNORE).
            const data = new FormData();
            data.append('entity_type', entityType);
            data.append('entity_id', entityId);

            fetch('/urls/' + result.id + '/attach', {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.json())
                .then(function (json) {
                    if (!json.success) {
                        showEntityFeedback(row, 'Fehler', 'error');
                        return;
                    }
                    appendUrlToList(row, result);
                    showEntityFeedback(row, 'Hinzugefügt ✓', 'success');
                })
                .catch(function () {
                    showEntityFeedback(row, 'Verbindungsfehler', 'error');
                });
        }

        function appendUrlToList(row, result) {
            let list = row.querySelector('.event-url-list');
            if (!list) {
                list = document.createElement('div');
                list.className = 'event-url-list p-2';
                addBtn?.closest('.edit-row-header')?.insertAdjacentElement('afterend', list);
            }
            // Remove the empty-state message (the <p class="text-muted">
            // itself) without removing its parent — the parent IS the
            // list container we're about to append the new item into.
            list.querySelector('p.text-muted')?.remove();

            const item = document.createElement('div');
            item.className = 'event-url-item';
            item.dataset.urlId = result.id;
            item.innerHTML =
                '<a href="' + (result.url || '#') + '" target="_blank" rel="noopener">' +
                '<i class="ti ' + (result.icon ? result.icon : (result.type_label === 'Email' ? 'ti-mail' : 'ti-link')) + '"></i> ' +
                (result.label || result.type_label || result.url) +
                '</a>' +
                '<button class="entity-edit-btn border-0" data-action="edit-entity-url" ' +
                'data-url-id="' + result.id + '" ' +
                'data-url-type-id="' + (result.url_type_id ?? '') + '" ' +
                'data-url-value="' + (result.url || '').replace(/"/g, '&quot;') + '" ' +
                'data-url-label="' + (result.label || '').replace(/"/g, '&quot;') + '">' +
                '<i class="ti ti-pencil"></i></button>' +
                '<button class="entity-remove-btn border-0" data-action="remove-entity-url" data-url-id="' + result.id + '">' +
                '<i class="ti ti-trash"></i></button>';
            list.appendChild(item);
            bindEditUrl(item.querySelector('[data-action="edit-entity-url"]'), row);
            bindRemoveUrl(item.querySelector('[data-action="remove-entity-url"]'), row);
        }

        function bindEditUrl(btn, row) {
            if (!btn || btn._editUrlInitialized) return;
            btn._editUrlInitialized = true;

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const urlId = btn.dataset.urlId;
                const currentTypeId = btn.dataset.urlTypeId;
                const currentUrl = btn.dataset.urlValue;
                const currentLabel = btn.dataset.urlLabel;

                fetch('/urls/types', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(res => res.json())
                    .then(function (json) {
                        const typeOptions = (json.success ? json.types : []).map(function (t) {
                            return { value: t.id, label: t.label };
                        });

                        openAttachEntityModal({
                            mode: 'edit',
                            title: 'Link bearbeiten',
                            confirmLabel: 'Speichern',
                            addEndpoint: '/urls/' + urlId + '/save',
                            addFields: [
                                { name: 'url_type_id', label: 'Typ', type: 'select', required: true, options: typeOptions, value: currentTypeId },
                                { name: 'url', label: 'URL', type: 'text', required: true, placeholder: 'https://...', value: currentUrl },
                                { name: 'label', label: 'Bezeichnung', type: 'text', value: currentLabel }
                            ],
                            previewFn: function (values) { return validateAndPreview(values, typeOptions); },
                            onSelected: function (result) {
                                if (!result.success) {
                                    showEntityFeedback(row, 'Fehler', 'error');
                                    return;
                                }
                                const item = row.querySelector('.event-url-item[data-url-id="' + urlId + '"]');
                                item?.remove();
                                appendUrlToList(row, {
                                    id: urlId,
                                    url: result.url,
                                    label: result.label,
                                    url_type_id: result.url_type_id,
                                    type_label: result.type_label,
                                    icon: result.icon
                                });
                                showEntityFeedback(row, 'Gespeichert ✓', 'success');
                            }
                        });
                    })
                    .catch(function () {
                        showEntityFeedback(row, 'Verbindungsfehler', 'error');
                    });
            });
        }

        function bindRemoveUrl(btn, row) {
            if (!btn || btn._removeUrlInitialized) return;
            btn._removeUrlInitialized = true;

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const urlId = btn.dataset.urlId;
                performUnlink(urlId, false);
            });

            function performUnlink(urlId, confirmed) {
                const data = new FormData();
                data.append('entity_type', entityType);
                data.append('entity_id', entityId);
                if (confirmed) data.append('confirmed', '1');

                fetch('/urls/' + urlId + '/unlink', {
                    method: 'POST',
                    body: data,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        if (!json.success) {
                            showEntityFeedback(row, 'Fehler', 'error');
                            return;
                        }
                        if (json.needsConfirmation) {
                            openConfirmModal({
                                title: 'Link entfernen',
                                message: 'Dieser Link ist nirgendwo sonst verknüpft. Beim Entfernen wird er endgültig gelöscht.',
                                confirmLabel: 'Endgültig entfernen',
                                onConfirm: function () {
                                    performUnlink(urlId, true);
                                }
                            });
                            return;
                        }
                        btn.closest('.event-url-item')?.remove();
                        const list = row.querySelector('.event-url-list');
                        if (list && list.querySelectorAll('.event-url-item').length === 0) {
                            list.innerHTML =
                                '<p class="text-muted p-2 mb-0">' +
                                '<i class="ti ti-link-off"></i> Noch keine Links' +
                                '</p>';
                        }
                        showEntityFeedback(row, 'Entfernt ✓', 'success');
                    })
                    .catch(function () {
                        showEntityFeedback(row, 'Verbindungsfehler', 'error');
                    });
            }
        }

        row.querySelectorAll('[data-action="remove-entity-url"]').forEach(function (btn) {
            bindRemoveUrl(btn, row);
        });

        row.querySelectorAll('[data-action="edit-entity-url"]').forEach(function (btn) {
            bindEditUrl(btn, row);
        });
    });

    // ── Media edit rows (promo + gallery) ─────────────────────
    document.querySelectorAll('.media-edit-row').forEach(function (row) {
        const pencilBtn = row.querySelector('.media-pencil-btn');
        const cancelBtn = row.querySelector('.media-cancel-btn');
        const entityType = row.dataset.entityType;
        const entityId = row.dataset.entityId;
        const entitySlug = row.dataset.entitySlug ?? entityId;
        const stage = row.dataset.stage;

        // Pencil — activate
        pencilBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            row.classList.add('editing');
            document.body.classList.add('is-editing');
        });

        // Cancel — deactivate
        cancelBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            row.classList.remove('editing');
            row.classList.remove('has-selection');
            document.body.classList.remove('is-editing');

            // Clear any gallery photo selection on close
            row.querySelectorAll('.gallery-checkbox:checked').forEach(function (cb) {
                cb.checked = false;
                cb.closest('.gallery-item')?.classList.remove('selected');
            });
            const checkAll = row.querySelector('.gallery-checkbox-all');
            if (checkAll) checkAll.checked = false;
            const feedback = row.querySelector('.edit-row-header .entity-feedback');
            if (feedback) feedback.textContent = '';
        });

        initUploadInputs(row);
        initMediaDeleteBtns(row);
        initPromoMetaButtons(row);

        const promoContent = row.querySelector('.media-promo-content');
        if (promoContent) {
            updatePromoCount(row, promoContent);
        }
    });

    function initPromoMetaButtons(row) {
        if (row._promoMetaInitialized) return;
        row._promoMetaInitialized = true;

        // Delegated on the row itself, since promo content gets replaced
        // via innerHTML after every upload/delete — a listener on the row
        // survives that, individual button listeners would not.
        row.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-action="edit-image-caption"], [data-action="edit-image-credit"]');
            if (!btn) return;
            e.stopPropagation();

            const mediaId = btn.dataset.mediaId;
            const field = btn.dataset.action === 'edit-image-credit' ? 'credit' : 'caption';
            const existingValue = field === 'credit' ? (btn.dataset.credit || '') : (btn.dataset.caption || '');

            openInputModal({
                title: field === 'credit' ? 'Credit für dieses Bild' : 'Caption für dieses Bild',
                placeholder: field === 'credit' ? '© Fotografin / Fotograf' : 'Bildbeschreibung...',
                initialValue: existingValue || '',
                onConfirm: function (value) {
                    const data = new FormData();
                    data.append(field, value);

                    fetch('/media/' + mediaId + '/meta', {
                        method: 'POST',
                        body: data,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(res => res.json())
                        .then(function (json) {
                            if (!json.success) {
                                showEntityFeedback(row, 'Fehler', 'error');
                                return;
                            }
                            // Keep the button's own data attribute in sync
                            // so re-opening the modal reflects what was
                            // just saved, not the value from page load.
                            btn.dataset[field] = value;

                            const targetBtn = row.querySelector('[data-media-id="' + mediaId + '"][data-action="edit-image-' + field + '"]');
                            const targetWrap = targetBtn?.closest('.img-placeholder') ?? targetBtn?.closest('.carousel-item');
                            const container = targetWrap?.parentElement;
                            if (container) {
                                let meta = container.querySelector('.image-meta');
                                if (!meta) {
                                    meta = document.createElement('small');
                                    meta.className = 'image-meta';
                                    container.appendChild(meta);
                                }
                                let span = meta.querySelector(field === 'credit' ? '.image-credit' : 'span:not(.image-credit)');
                                if (!span) {
                                    span = document.createElement('span');
                                    if (field === 'credit') span.className = 'image-credit';
                                    meta.appendChild(span);
                                }
                                span.textContent = field === 'credit' ? (value ? '📷 ' + value : '') : value;
                                if (!value) span.remove();
                                if (!meta.querySelector('span')) meta.remove();
                            }
                            closeInputModal();
                            showEntityFeedback(row, 'Gespeichert ✓', 'success');
                        })
                        .catch(function () {
                            showEntityFeedback(row, 'Verbindungsfehler', 'error');
                        });
                }
            });
        });
    }

    function updatePromoCount(row, content) {
        const label = row.querySelector('.edit-row-label .media-count');
        if (!label) return;
        const count = content.querySelectorAll('img').length;
        const carousel = content.querySelector('.carousel');

        if (carousel) {
            const items = carousel.querySelectorAll('.carousel-item');
            const activeIndex = Array.from(items).findIndex(function (item) {
                return item.classList.contains('active');
            });
            label.textContent = '(' + (activeIndex + 1) + '/' + count + ')';

            carousel.addEventListener('slid.bs.carousel', function (e) {
                label.textContent = '(' + (e.to + 1) + '/' + count + ')';
            });
        } else {
            label.textContent = '(' + count + ')';
        }
    }

    function initUploadInputs(row) {
        row.querySelectorAll('[data-action="upload-entity-image"]').forEach(function (input) {
            if (input._uploadInitialized) return;
            input._uploadInitialized = true;
            input.addEventListener('change', function () {
                if (!this.files[0]) return;
                const entityType = row.dataset.entityType;
                const entityId = row.dataset.entityId;
                const entitySlug = row.dataset.entitySlug ?? entityId;
                const stage = row.dataset.stage;
                const file = this.files[0];
                const timestamp = Date.now();
                const publicId = entityType + '-' + entitySlug + '-' + stage + '-' + timestamp;
                const data = new FormData();
                data.append('image', file);
                data.append('entity_type', entityType);
                data.append('entity_id', entityId);
                data.append('stage', stage);
                data.append('public_id', publicId);

                showEntityFeedback(row, 'Wird hochgeladen...', 'success', { persistent: true });

                fetch('/media/upload', {
                    method: 'POST',
                    body: data,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        if (json.success) {
                            if (stage === 'promo') {
                                // Always re-fetch the freshly rendered fragment
                                // rather than hand-building HTML — this correctly
                                // handles going from 0→1 images, 1→2 (upgrading
                                // to a carousel), and any image count beyond that.
                                const content = row.querySelector('.media-promo-content');
                                if (content) {
                                    fetch('/events/' + entityId + '/promo-fragment', {
                                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                    })
                                        .then(res => res.text())
                                        .then(function (html) {
                                            content.innerHTML = html;
                                            initUploadInputs(row);
                                            initMediaDeleteBtns(row);
                                            updatePromoCount(row, content);
                                            showEntityFeedback(row, 'Hochgeladen ✓', 'success');
                                        })
                                        .catch(function () {
                                            showEntityFeedback(row, 'Verbindungsfehler', 'error');
                                        });
                                }
                            } else {
                                // Gallery — append new item
                                const grid = row.querySelector('.media-gallery-grid');
                                if (grid) {
                                    const col = document.createElement('div');
                                    col.className = 'col-6 col-md-4 col-lg-3 gallery-item';
                                    col.dataset.mediaId = json.id;
                                    col.innerHTML =
                                        '<div class="img-placeholder event-gallery-img">' +
                                        '<img src="' + json.media_url + '" class="zoomable">' +
                                        '<div class="image-edit-overlay">' +
                                        '<button class="section-control-btn" data-action="delete-entity-image" data-media-id="' + json.id + '" data-entity-type="' + entityType + '" data-entity-id="' + entityId + '">' +
                                        '<i class="ti ti-trash"></i></button>' +
                                        '</div></div>';
                                    grid.appendChild(col);
                                    initMediaDeleteBtns(row);
                                }
                            }
                            showEntityFeedback(row, 'Hochgeladen ✓', 'success');
                            this.value = '';
                        } else {
                            showEntityFeedback(row, 'Upload fehlgeschlagen', 'error');
                        }
                    }.bind(this))
                    .catch(function () {
                        showEntityFeedback(row, 'Verbindungsfehler', 'error');
                    });
            });
        });
    }

    function initMediaDeleteBtns(row) {
        row.querySelectorAll('[data-action="delete-entity-image"]').forEach(function (btn) {
            if (btn._deleteInitialized) return;
            btn._deleteInitialized = true;
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (!confirm('Bild löschen?')) return;
                const mediaId = btn.dataset.mediaId;
                const entityType = btn.dataset.entityType;
                const entityId = btn.dataset.entityId;
                const data = new FormData();
                data.append('entity_type', entityType);
                data.append('entity_id', entityId);
                fetch('/media/' + mediaId + '/delete', {
                    method: 'POST',
                    body: data,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        if (!json.success) {
                            showEntityFeedback(row, 'Fehler', 'error');
                            return;
                        }

                        const isGalleryItem = !!btn.closest('.gallery-item');

                        if (isGalleryItem) {
                            // Gallery: remove the item, empty-state placeholder
                            // is handled separately when grid becomes empty.
                            btn.closest('.gallery-item')?.remove();
                            const grid = row.querySelector('.media-gallery-grid');
                            const remaining = grid ? grid.querySelectorAll('.gallery-item').length : 0;
                            if (grid && remaining === 0) {
                                grid.innerHTML =
                                    '<div class="col-12"><p class="text-muted p-2">' +
                                    '<i class="ti ti-photo-off"></i> ' +
                                    'Noch keine Galeriebilder' +
                                    '</p></div>';
                            }
                            row.classList.toggle('has-items', remaining > 0);
                            showEntityFeedback(row, 'Gelöscht ✓', 'success');
                            return;
                        }

                        // Promo image deletion — fetch the freshly rebuilt
                        // fragment (single image / carousel / placeholder)
                        // from the server and swap it in, no reload needed
                        // for any promo state.
                        const content = row.querySelector('.media-promo-content');
                        if (content) {
                            fetch('/events/' + entityId + '/promo-fragment', {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            })
                                .then(res => res.text())
                                .then(function (html) {
                                    content.innerHTML = html;
                                    // Re-wire delete buttons and any upload
                                    // input present in the fresh markup
                                    // (the placeholder's upload button, if
                                    // the gallery dropped back to zero images).
                                    initMediaDeleteBtns(row);
                                    initUploadInputs(row);
                                    updatePromoCount(row, content);
                                    showEntityFeedback(row, 'Gelöscht ✓', 'success');
                                })
                                .catch(function () {
                                    showEntityFeedback(row, 'Verbindungsfehler', 'error');
                                });
                        }
                    })
                    .catch(function () { showEntityFeedback(row, 'Verbindungsfehler', 'error'); });
            });
        });
    }

    // ──────────────────────────────────────────────────────────
    // GALLERY DRAG-AND-DROP BATCH UPLOAD
    // (separate from the single-file header upload above —
    //  targets .media-dropzone / .media-upload-confirm only)
    // ──────────────────────────────────────────────────────────

    document.querySelectorAll('.media-edit-row[data-stage="gallery"]').forEach(function (row) {
        const entityType = row.dataset.entityType;
        const entityId = row.dataset.entityId;
        const entitySlug = row.dataset.entitySlug ?? entityId;
        const stage = row.dataset.stage;
        const dropzone = row.querySelector('.media-dropzone');
        const fileInput = row.querySelector('.media-file-input');
        const uploadBtn = row.querySelector('.media-upload-confirm');
        const progress = row.querySelector('.media-upload-progress');

        const dropzoneLabel = row.querySelector('.media-dropzone-label');
        const dropzoneDefaultText = dropzoneLabel?.textContent ?? '';

        function updateDropzoneLabel() {
            const count = fileInput?.files?.length ?? 0;
            if (!dropzoneLabel) return;
            dropzoneLabel.textContent = count > 0
                ? count + ' Datei' + (count > 1 ? 'en' : '') + ' ausgewählt'
                : dropzoneDefaultText;
        }

        dropzone?.addEventListener('dragover', function (e) {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });

        dropzone?.addEventListener('dragleave', function () {
            dropzone.classList.remove('dragover');
        });

        dropzone?.addEventListener('drop', function (e) {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            if (fileInput) fileInput.files = e.dataTransfer.files;
            dropzone.classList.toggle('has-files', !!fileInput?.files?.length);
            updateDropzoneLabel();
        });

        // Native file picker (click on the dropzone opens this input)
        fileInput?.addEventListener('change', function () {
            dropzone.classList.toggle('has-files', !!fileInput.files?.length);
            updateDropzoneLabel();
        });

        uploadBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            const files = fileInput?.files;
            if (!files || files.length === 0) return;
            uploadGalleryBatch(row, files, entityType, entityId, entitySlug, stage);
        });

        function uploadGalleryBatch(row, files, entityType, entityId, entitySlug, stage) {
            const total = files.length;
            let index = 0;

            function uploadNext() {
                if (index >= total) {
                    if (progress) progress.textContent = total + ' Foto(s) hochgeladen ✓';
                    setTimeout(function () { if (progress) progress.textContent = ''; }, 3000);
                    if (fileInput) fileInput.value = '';
                    dropzone?.classList.remove('has-files');
                    updateDropzoneLabel();
                    return;
                }

                const file = files[index];
                const timestamp = Date.now();
                const publicId = entityType + '-' + entitySlug + '-' + stage + '-' + (index + 1) + '-' + timestamp;
                const data = new FormData();

                data.append('image', file);
                data.append('entity_type', entityType);
                data.append('entity_id', entityId);
                data.append('stage', stage);
                data.append('public_id', publicId);

                if (progress) progress.textContent = 'Hochladen ' + (index + 1) + ' / ' + total + '...';

                fetch('/media/upload', {
                    method: 'POST',
                    body: data,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        if (json.success) {
                            appendGalleryItem(row, json, entityType, entityId);
                        }
                        index++;
                        uploadNext();
                    })
                    .catch(function () {
                        if (progress) progress.textContent = 'Fehler bei Foto ' + (index + 1);
                        index++;
                        uploadNext();
                    });
            }

            uploadNext();
        }

        function appendGalleryItem(row, json, entityType, entityId) {
            const grid = row.querySelector('.media-gallery-grid');
            if (!grid) return;

            // Remove empty-state placeholder if present
            grid.querySelector('.col-12 .text-muted')?.closest('.col-12')?.remove();

            const col = document.createElement('div');
            col.className = 'col-6 col-md-4 col-lg-3 gallery-item';
            col.dataset.mediaId = json.id;
            col.innerHTML =
                '<label class="gallery-item-checkbox">' +
                '<input type="checkbox" class="gallery-checkbox" value="' + json.id + '" data-caption="" data-credit="">' +
                '</label>' +
                '<div class="img-placeholder event-gallery-img">' +
                '<img src="' + json.media_url + '" class="zoomable">' +
                '<div class="image-edit-overlay">' +
                '<button class="section-control-btn" data-action="delete-entity-image"' +
                ' data-media-id="' + json.id + '"' +
                ' data-entity-type="' + entityType + '"' +
                ' data-entity-id="' + entityId + '">' +
                '<i class="ti ti-trash"></i></button>' +
                '</div></div>';
            grid.appendChild(col);
            initMediaDeleteBtns(row);
            initGalleryCheckbox(col);
            updateHasItems();
        }

        function initGalleryCheckbox(col) {
            // Selection state changes are handled by the row-level
            // delegated listener below — nothing needed here beyond
            // letting the native checkbox behave normally.
        }

        // ── Selection state + header buttons ──────────────────
        const checkAll = row.querySelector('.gallery-checkbox-all');
        const btnCaption = row.querySelector('.gallery-btn-caption');
        const btnCredit = row.querySelector('.gallery-btn-credit');
        const btnDelete = row.querySelector('.gallery-btn-delete');

        function getSelected() {
            return Array.from(row.querySelectorAll('.gallery-checkbox:checked'));
        }

        function updateHeaderBtns() {
            const count = getSelected().length;
            const any = count > 0;
            row.classList.toggle('has-selection', any);
            const feedback = row.querySelector('.edit-row-header .entity-feedback');
            if (feedback) {
                feedback.textContent = any ? count + ' Foto' + (count > 1 ? 's' : '') + ' ausgewählt' : '';
            }
        }

        function updateHasItems() {
            const grid = row.querySelector('.media-gallery-grid');
            const hasItems = !!grid && grid.querySelectorAll('.gallery-item').length > 0;
            row.classList.toggle('has-items', hasItems);
        }

        updateHasItems();

        row.addEventListener('change', function (e) {
            if (e.target.classList.contains('gallery-checkbox')) {
                const item = e.target.closest('.gallery-item');
                if (item) item.classList.toggle('selected', e.target.checked);
                updateHeaderBtns();
                const all = row.querySelectorAll('.gallery-checkbox');
                const checked = row.querySelectorAll('.gallery-checkbox:checked');
                if (checkAll) checkAll.checked = all.length === checked.length && all.length > 0;
            }
        });

        checkAll?.addEventListener('change', function () {
            row.querySelectorAll('.gallery-checkbox').forEach(function (cb) {
                cb.checked = checkAll.checked;
                cb.closest('.gallery-item')?.classList.toggle('selected', checkAll.checked);
            });
            updateHeaderBtns();
        });

        // ── Caption / Credit modal ─────────────────────────────
        btnCaption?.addEventListener('click', function () {
            openBatchMetaModal('caption');
        });

        btnCredit?.addEventListener('click', function () {
            openBatchMetaModal('credit');
        });

        btnDelete?.addEventListener('click', function () {
            const selected = getSelected();
            const count = selected.length;
            if (count === 0) return;

            const confirmMsg = count === 1
                ? 'Dieses Foto löschen?'
                : count + ' Fotos löschen?';
            if (!confirm(confirmMsg)) return;

            const items = selected.map(cb => cb.closest('.gallery-item')).filter(Boolean);
            let index = 0;

            function deleteNext() {
                if (index >= items.length) {
                    const grid = row.querySelector('.media-gallery-grid');
                    if (grid && grid.querySelectorAll('.gallery-item').length === 0) {
                        grid.innerHTML =
                            '<div class="col-12"><p class="text-muted p-2">' +
                            '<i class="ti ti-photo-off"></i> ' +
                            'Noch keine Galeriebilder — Bearbeitungsmodus aktivieren um Fotos hochzuladen.' +
                            '</p></div>';
                    }
                    if (checkAll) checkAll.checked = false;
                    updateHasItems();
                    updateHeaderBtns();
                    showEntityFeedback(row, 'Gelöscht ✓', 'success');
                    return;
                }

                const item = items[index];
                const mediaId = item.dataset.mediaId;
                const data = new FormData();
                data.append('entity_type', entityType);
                data.append('entity_id', entityId);

                const feedback = row.querySelector('.edit-row-header .entity-feedback');
                if (feedback) feedback.textContent = 'Löschen ' + (index + 1) + ' / ' + items.length + '...';

                fetch('/media/' + mediaId + '/delete', {
                    method: 'POST',
                    body: data,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        if (json.success) item.remove();
                        index++;
                        deleteNext();
                    })
                    .catch(function () {
                        index++;
                        deleteNext();
                    });
            }

            deleteNext();
        });

        function getExistingMetaValue(checkbox, field) {
            return field === 'credit'
                ? (checkbox.dataset.credit || '')
                : (checkbox.dataset.caption || '');
        }

        function openBatchMetaModal(field) {
            const selected = getSelected();
            const count = selected.length;

            // Pre-fill only when every selected photo agrees on the
            // same value (including all being blank). If they differ,
            // there's no single correct value to show — leave it blank
            // but tell the editor why, so they don't mistake "blank"
            // for "none of these have a value yet".
            const values = selected.map(function (cb) {
                return getExistingMetaValue(cb, field);
            });
            const allAgree = values.every(function (v) {
                return v === values[0];
            });

            const defaultPlaceholder = field === 'credit' ? '© Fotografin / Fotograf' : 'Bildbeschreibung...';
            const mixedPlaceholder = 'Unterschiedliche Einträge — neuer Wert wird auf alle angewendet';

            openInputModal({
                title: (field === 'credit' ? 'Credit für ' : 'Caption für ') + count + ' Foto' + (count > 1 ? 's' : ''),
                placeholder: allAgree ? defaultPlaceholder : mixedPlaceholder,
                initialValue: allAgree ? values[0] : '',
                onConfirm: function (value) {
                    const selected = getSelected();
                    const ids = selected.map(cb => cb.value);
                    if (ids.length === 0) return;

                    const data = new FormData();
                    ids.forEach(id => data.append('ids[]', id));
                    data.append(field, value);

                    fetch('/media/batch-meta', {
                        method: 'POST',
                        body: data,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(res => res.json())
                        .then(function (json) {
                            if (json.success) {
                                selected.forEach(function (cb) {
                                    cb.dataset[field] = value;

                                    const item = cb.closest('.gallery-item');
                                    if (!item) return;
                                    let meta = item.querySelector('.gallery-item-meta');
                                    if (!meta) {
                                        meta = document.createElement('small');
                                        meta.className = 'gallery-item-meta';
                                        item.appendChild(meta);
                                    }
                                    let span = meta.querySelector(field === 'credit' ? '.image-credit' : 'span:not(.image-credit)');
                                    if (!span) {
                                        span = document.createElement('span');
                                        if (field === 'credit') span.className = 'image-credit';
                                        meta.appendChild(span);
                                    }
                                    span.textContent = field === 'credit'
                                        ? (value ? '📷 ' + value : '')
                                        : value;
                                    if (!value) span.remove();
                                });
                                closeInputModal();
                                showEntityFeedback(row, 'Gespeichert ✓', 'success');
                            } else {
                                showEntityFeedback(row, 'Fehler', 'error');
                            }
                        })
                        .catch(function () { showEntityFeedback(row, 'Verbindungsfehler', 'error'); });
                }
            });
        }
    });

    // ── New event ─────────────────────────────────────────────
    document.querySelector('[data-action="new-event"]')?.addEventListener('click', function (e) {
        e.preventDefault();
        fetch('/events/add', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(function (json) {
                if (json.success && json.slug) {
                    window.location.href = '/veranstaltungen/' + json.slug;
                } else {
                    alert('Fehler beim Erstellen der Veranstaltung.');
                }
            })
            .catch(function () {
                alert('Verbindungsfehler.');
            });
    });

    // ── Warn on page leave ────────────────────────────────────
    window.addEventListener('beforeunload', function (e) {
        if (hasUnsaved) { e.preventDefault(); e.returnValue = ''; }
    });

});