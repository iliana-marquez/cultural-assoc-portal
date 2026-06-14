/**
 * edit-mode.js
 * kulturCMS — Inline edit mode for sections and entity records.
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

    function showEntityFeedback(row, message, type) {
        let fb = row.querySelector('.entity-feedback');
        if (!fb) {
            fb = document.createElement('span');
            fb.className = 'entity-feedback';
            row.appendChild(fb);
        }
        fb.textContent = message;
        fb.className = 'entity-feedback entity-feedback--' + type;
        setTimeout(function () { fb.textContent = ''; }, 3000);
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

    // ── Warn on page leave ────────────────────────────────────
    window.addEventListener('beforeunload', function (e) {
        if (hasUnsaved) { e.preventDefault(); e.returnValue = ''; }
    });

});