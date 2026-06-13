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
        // Warn if another block has unsaved changes
        if (activeBlock && activeBlock !== block && hasUnsaved) {
            if (!confirm('Ungespeicherte Änderungen verwerfen?')) return;
            cancelBlock(activeBlock);
        }
        if (activeBlock && activeBlock !== block) {
            deactivateBlock(activeBlock);
        }

        activeBlock = block;
        block.classList.add('editing');

        // Store originals + make fields editable
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

        // Clone toggle controls to clear stale listeners
        const editControls = block.querySelector('.block-edit-controls');
        if (editControls) {
            const fresh = editControls.cloneNode(true);
            editControls.replaceWith(fresh);
        }

        // Init toggles and image controls on fresh clone
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
        // Reset save button state
        const saveBtn = block.querySelector('.btn-save');
        if (saveBtn) {
            saveBtn.disabled = false;
            const icon = saveBtn.querySelector('i');
            if (icon) icon.className = 'ti ti-check';
        }
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

        // Text fields
        block.querySelectorAll('[data-field]').forEach(function (el) {
            data.append(el.dataset.field, el.innerText.trim());
        });

        // Toggle values
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
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.querySelector('i').className = 'ti ti-check';
                    }
                }
            })
            .catch(function () {
                showBlockFeedback(block, 'Verbindungsfehler', 'error');
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.querySelector('i').className = 'ti ti-check';
                }
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
                const layouts = ['50-50', '75-25', '25-75'];
                const toggle = btn.closest('[data-toggle]');
                const next = layouts[(layouts.indexOf(toggle.dataset.value) + 1) % layouts.length];
                toggle.dataset.value = next;
                btn.querySelector('.layout-label').textContent = next;
                // Update column classes
                updateColumns(block, next);
                hasUnsaved = true;
            });
        });

        // Flip image position
        block.querySelectorAll('[data-action="toggle-flip"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const toggle = btn.closest('[data-toggle]');
                const current = toggle.dataset.value;
                const next = current === 'left' ? 'right' : 'left';
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
                const img = block.querySelector('.section-image');
                if (img) img.style.objectFit = next;
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

    // ── Column update helpers ─────────────────────────────────

    function updateColumns(block, layout) {
        const row = block.querySelector('.row');
        if (!row) return;
        const cols = {
            '50-50': { text: 'col-12 col-md-6', image: 'col-12 col-md-6' },
            '75-25': { text: 'col-12 col-md-8', image: 'col-12 col-md-4' },
            '25-75': { text: 'col-12 col-md-4', image: 'col-12 col-md-8' },
        };
        const c = cols[layout] || cols['50-50'];
        const textCol = row.querySelector('.section-content')?.closest('[class*="col-"]');
        const imageCol = row.querySelector('.section-image-wrap, .section-image-placeholder')?.closest('[class*="col-"]');
        if (textCol) textCol.className = c.text;
        if (imageCol) imageCol.className = c.image;
    }

    function flipImage(block, position) {
        const row = block.querySelector('.row');
        if (!row) return;
        const imageCol = row.querySelector('.section-image-wrap, .section-image-placeholder')?.closest('[class*="col-"]');
        const contentCol = row.querySelector('.section-content')?.closest('[class*="col-"]');
        if (!imageCol || !contentCol) return;
        if (position === 'left') {
            row.insertBefore(imageCol, contentCol);
        } else {
            row.appendChild(imageCol);
        }
    }

    // ── Image controls ────────────────────────────────────────

    function initImageControls(block) {
        const sectionId = block.dataset.sectionId;

        // Upload section image
        block.querySelector('[data-action="upload-image"]')?.addEventListener('change', function () {
            if (!this.files[0]) return;
            uploadSectionImage(block, this.files[0], 'image', sectionId);
        });

        // Remove section image
        block.querySelector('[data-action="remove-image"]')?.addEventListener('click', function () {
            if (!confirm('Bild entfernen?')) return;
            removeSectionImage(block, 'image', sectionId);
        });

        // Upload BG image
        block.querySelector('[data-action="upload-bg"]')?.addEventListener('change', function () {
            if (!this.files[0]) return;
            uploadSectionImage(block, this.files[0], 'bg_image', sectionId);
        });

        // Remove BG image
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
                        // Replace placeholder or update existing image
                        const placeholder = block.querySelector('.section-image-placeholder');
                        const existingImg = block.querySelector('.section-image');
                        if (placeholder) {
                            placeholder.outerHTML =
                                '<div class="section-image-wrap">' +
                                '<img src="' + json.url + '" class="section-image zoomable">' +
                                '</div>';
                        } else if (existingImg) {
                            existingImg.src = json.url;
                        }
                        // Show image controls, hide no-image controls
                        block.querySelectorAll('.ctrl-no-image').forEach(el => el.classList.add('d-none'));
                        block.querySelectorAll('.ctrl-has-image').forEach(el => el.classList.remove('d-none'));
                    } else {
                        // BG image
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
                        const wrap = block.querySelector('.section-image-wrap');
                        if (wrap) {
                            wrap.outerHTML =
                                '<div class="section-image-placeholder">' +
                                '<i class="ti ti-photo"></i></div>';
                        }
                        block.querySelectorAll('.ctrl-has-image').forEach(el => el.classList.add('d-none'));
                        block.querySelectorAll('.ctrl-no-image').forEach(el => el.classList.remove('d-none'));
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

    // ── Init section blocks — attach save/cancel ONCE ─────────

    document.querySelectorAll('.editable-block').forEach(function (block) {

        // Pencil — activates block only if inactive (ONCE)
        block.querySelector('.btn-edit')?.addEventListener('click', function (e) {
            e.stopPropagation();
            if (!block.classList.contains('editing')) {
                activateBlock(block);
            }
        });

        // Save — attached ONCE
        block.querySelector('.btn-save')?.addEventListener('click', function (e) {
            e.stopPropagation();
            saveBlock(block);
        });

        // Cancel — attached ONCE
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

        editBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            original = field.innerText;
            field.contentEditable = 'true';
            field.classList.add('editing');
            row.classList.add('editing');
            field.focus();
        });

        cancelBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            field.innerText = original;
            field.contentEditable = 'false';
            field.classList.remove('editing');
            row.classList.remove('editing');
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
                        field.classList.remove('editing');
                        row.classList.remove('editing');
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