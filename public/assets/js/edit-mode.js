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
            openConfirmModal({
                title: 'Ungespeicherte Änderungen',
                message: 'Möchtest du die ungespeicherten Änderungen verwerfen?',
                confirmLabel: 'Verwerfen',
                onConfirm: function () {
                    cancelBlock(activeBlock);
                    doActivateBlock(block);
                }
            });
            return;
        }
        if (activeBlock && activeBlock !== block) deactivateBlock(activeBlock);
        doActivateBlock(block);
    }

    function doActivateBlock(block) {
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
        initRichTextToolbar(block);
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
            data.append(el.dataset.field, el.innerHTML.trim());
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
                openConfirmModal({
                    title: 'Bild entfernen',
                    message: 'Dieses Bild wirklich entfernen?',
                    confirmLabel: 'Entfernen',
                    onConfirm: function () {
                        removeSectionImage(block, 'image', sectionId);
                    }
                });
            });
        });

        block.querySelector('[data-action="upload-bg"]')?.addEventListener('change', function () {
            if (!this.files[0]) return;
            uploadSectionImage(block, this.files[0], 'bg_image', sectionId);
        });

        block.querySelector('[data-action="remove-bg"]')?.addEventListener('click', function () {
            openConfirmModal({
                title: 'Hintergrundbild entfernen',
                message: 'Dieses Hintergrundbild wirklich entfernen?',
                confirmLabel: 'Entfernen',
                onConfirm: function () {
                    removeSectionImage(block, 'bg_image', sectionId);
                }
            });
        });

        // Credit button — inside image overlay, opens shared input modal.
        // Updates hidden data-field="image_credit" so saveBlock() picks it up,
        // and live-updates the visible credit display below the image.
        block.querySelectorAll('[data-action="edit-section-credit"]').forEach(function (btn) {
            if (btn._creditInitialized) return;
            btn._creditInitialized = true;

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                openInputModal({
                    title: 'Bildcredit',
                    placeholder: '© Fotografin / Fotograf',
                    initialValue: btn.dataset.credit || '',
                    onConfirm: function (value) {
                        const data = new FormData();
                        data.append('image_credit', value);

                        fetch('/page/section/' + sectionId + '/save', {
                            method: 'POST',
                            body: data,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                            .then(res => res.json())
                            .then(function (json) {
                                if (!json.success) return;

                                btn.dataset.credit = value;

                                const imageCol = btn.closest('.section-image-col');
                                let creditDisplay = imageCol?.querySelector('.image-credit');
                                if (value) {
                                    if (!creditDisplay) {
                                        creditDisplay = document.createElement('span');
                                        creditDisplay.className = 'image-credit small';
                                        creditDisplay.innerHTML = '<i class="ti ti-camera"></i> <span class="image-credit-text"></span>';
                                        imageCol?.querySelector('.img-placeholder')?.insertAdjacentElement('afterend', creditDisplay);
                                    }
                                    creditDisplay.querySelector('.image-credit-text').textContent = value;
                                } else {
                                    creditDisplay?.remove();
                                }

                                closeInputModal();
                                showBlockFeedback(block, 'Credit gespeichert ✓', 'success');
                            })
                            .catch(function () {
                                showBlockFeedback(block, 'Verbindungsfehler', 'error');
                            });

                    }
                });
            });
        });
    }

    // ── Rich-text toolbar ───────────────────────────────────────
    // Called fresh on every activation, exactly like initToggles()/
    // initImageControls() — NOT a separate, page-load-only script
    // with document-level delegation. doActivateBlock() clones
    // .block-edit-controls (which this toolbar's buttons live
    // inside) on every activation to clear ITS OWN stale listeners;
    // a listener attached once at page load has no way to survive
    // that. Scoping setup to THIS block, called every time it
    // activates, sidesteps the problem entirely rather than trying
    // to out-guess the clone's timing.
    function initRichTextToolbar(block) {
        let savedRange = null;

        // Get the editable field containing a DOM node
        function fieldOf(node) {
            if (!node) return null;
            const el = node.nodeType === 1 ? node : node.parentElement;
            const field = el?.closest('[data-field].editable-field');
            return (field && block.contains(field)) ? field : null;
        }

        // Capture selection on interaction with any editable field in this block
        function saveSelection() {
            const sel = document.getSelection();
            if (!sel || sel.rangeCount === 0) return;
            if (fieldOf(sel.anchorNode)) {
                savedRange = sel.getRangeAt(0).cloneRange();
            }
        }

        block.querySelectorAll('[data-field]').forEach(function (field) {
            field.addEventListener('mouseup', saveSelection);
            field.addEventListener('keyup', saveSelection);
        });

        // Prevent selection loss when clicking toolbar buttons
        block.addEventListener('mousedown', function (e) {
            if (e.target.closest('[data-action^="richtext-"]')) {
                e.preventDefault();
            }
        });

        // Check if savedRange start is already inside a span with className.
        // Checked BEFORE restoring selection — avoids unreliable post-addRange state.
        function existingWrap(className) {
            if (!savedRange) return null;
            const startEl = savedRange.startContainer.nodeType === 1
                ? savedRange.startContainer
                : savedRange.startContainer.parentElement;
            const field = fieldOf(savedRange.startContainer);
            if (!field) return null;
            const span = startEl?.closest('.' + className);
            return (span && field.contains(span)) ? span : null;
        }

        function restoreSelection() {
            if (!savedRange) return;
            const sel = document.getSelection();
            sel.removeAllRanges();
            sel.addRange(savedRange);
        }

        // After any DOM mutation, re-snap savedRange to whatever is selected now.
        // This keeps savedRange valid across wrap/unwrap operations in the same
        // session — so the next toolbar click can correctly detect the new state
        // without requiring the editor to re-select text manually.
        function resnapSelection() {
            const sel = document.getSelection();
            if (sel && sel.rangeCount > 0) {
                savedRange = sel.getRangeAt(0).cloneRange();
            }
        }

        function applySpanClass(className) {
            if (!savedRange) return;

            const field = fieldOf(savedRange.startContainer);
            if (!field) return;

            // Toggle detection BEFORE touching focus or selection
            const existing = existingWrap(className);

            field.focus();
            restoreSelection();

            if (existing) {
                // ── Unwrap ────────────────────────────────────────────
                const parent = existing.parentNode;
                while (existing.firstChild) {
                    parent.insertBefore(existing.firstChild, existing);
                }
                existing.remove();
                field.normalize(); // merge adjacent text nodes after removal
            } else {
                // ── Wrap ──────────────────────────────────────────────
                const sel = document.getSelection();
                const hasText = sel.toString().trim() !== '';

                if (!hasText && (className === 'rt-ul' || className === 'rt-ol')) {
                    // List with no selection — wrap entire field content so the
                    // editor can click the list button without selecting text first
                    const span = document.createElement('span');
                    span.className = className;
                    while (field.firstChild) span.appendChild(field.firstChild);
                    field.appendChild(span);
                    // Select the new span so next click detects and toggles it off
                    const r = document.createRange();
                    r.selectNodeContents(span);
                    sel.removeAllRanges();
                    sel.addRange(r);

                } else if (hasText) {
                    const range = sel.getRangeAt(0);
                    const span = document.createElement('span');
                    span.className = className;
                    try {
                        range.surroundContents(span);
                    } catch (_) {
                        span.appendChild(range.extractContents());
                        range.insertNode(span);
                    }
                    // Update selection to inside the new span — enables immediate
                    // same-session toggle without requiring the editor to re-select
                    const r = document.createRange();
                    r.selectNodeContents(span);
                    sel.removeAllRanges();
                    sel.addRange(r);
                }
                // bold/italic with no selection: no-op — nothing to style
            }

            resnapSelection();
            field.dispatchEvent(new Event('input', { bubbles: true }));
        }

        function applyLinkFormat() {
            if (!savedRange) return;

            const field = fieldOf(savedRange.startContainer);
            if (!field) return;

            // Toggle off existing link — check BEFORE restoring selection
            const startEl = savedRange.startContainer.nodeType === 1
                ? savedRange.startContainer
                : savedRange.startContainer.parentElement;
            const existingLink = startEl?.closest('a');
            if (existingLink && field.contains(existingLink)) {
                field.focus();
                restoreSelection();
                const parent = existingLink.parentNode;
                while (existingLink.firstChild) {
                    parent.insertBefore(existingLink.firstChild, existingLink);
                }
                existingLink.remove();
                field.normalize();
                resnapSelection();
                field.dispatchEvent(new Event('input', { bubbles: true }));
                return;
            }

            field.focus();
            restoreSelection();

            const sel = document.getSelection();
            if (sel.toString().trim() === '') {
                alert('Bitte zuerst Text markieren, der verlinkt werden soll.');
                return;
            }

            const url = prompt('Link-Adresse:', 'https://');
            if (!url || url.trim() === '' || url.trim() === 'https://') return;

            let normalized = url.trim();
            if (!/^https?:\/\//i.test(normalized)) normalized = 'https://' + normalized;

            // prompt() causes focus loss — restore field focus and selection again
            field.focus();
            restoreSelection();

            const range = document.getSelection().getRangeAt(0);
            const a = document.createElement('a');
            a.href = normalized;
            a.target = '_blank';
            a.rel = 'noopener noreferrer';
            a.className = 'inline-link inline-link--content';
            try {
                range.surroundContents(a);
            } catch (_) {
                a.appendChild(range.extractContents());
                range.insertNode(a);
            }

            resnapSelection();
            field.dispatchEvent(new Event('input', { bubbles: true }));
        }

        block.querySelectorAll('[data-action^="richtext-"]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                switch (btn.dataset.action) {
                    case 'richtext-bold': applySpanClass('rt-bold'); break;
                    case 'richtext-italic': applySpanClass('rt-italic'); break;
                    case 'richtext-bullet-list': applySpanClass('rt-ul'); break;
                    case 'richtext-numbered-list': applySpanClass('rt-ol'); break;
                    case 'richtext-link': applyLinkFormat(); break;
                }
            });
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
                        // BG image — on its own .segment-bg layer so
                        // filter: grayscale() only affects the image,
                        // never the text content above it.
                        let bgLayer = block.querySelector('.segment-bg');
                        if (!bgLayer) {
                            bgLayer = document.createElement('div');
                            bgLayer.className = 'segment-bg';
                            block.querySelector('.segment').prepend(bgLayer);
                        }
                        bgLayer.style.backgroundImage = 'url(' + json.url + ')';

                        if (!block.querySelector('.segment-overlay')) {
                            const overlay = document.createElement('div');
                            overlay.className = 'segment-overlay';
                            block.querySelector('.segment').prepend(overlay);
                        }
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
                        block.querySelector('.segment-bg')?.remove();
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
            if (hasUnsaved) {
                openConfirmModal({
                    title: 'Ungespeicherte Änderungen',
                    message: 'Möchtest du die ungespeicherten Änderungen verwerfen?',
                    confirmLabel: 'Verwerfen',
                    onConfirm: function () { cancelBlock(block); }
                });
                return;
            }
            cancelBlock(block);
        });

        block.querySelector('.btn-delete-section')?.addEventListener('click', function (e) {
            e.stopPropagation();
            const sectionId = block.dataset.sectionId;
            if (!sectionId) return;

            openConfirmModal({
                title: 'Abschnitt löschen',
                message: 'Dieser Abschnitt und sein gesamter Inhalt (inklusive aller Buttons) werden endgültig gelöscht.',
                confirmLabel: 'Endgültig löschen',
                onConfirm: function () {
                    const data = new FormData();
                    fetch('/page/section/' + sectionId + '/delete', {
                        method: 'POST',
                        body: data,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(res => res.json())
                        .then(function (json) {
                            if (json.success) {
                                window.location.reload();
                            } else {
                                showBlockFeedback(block, 'Fehler beim Löschen', 'error');
                            }
                        })
                        .catch(function () {
                            showBlockFeedback(block, 'Verbindungsfehler', 'error');
                        });
                }
            });
        });

        function moveSection(direction) {
            const sectionId = block.dataset.sectionId;
            const myIndex = parseInt(block.dataset.orderIndex, 10);
            if (!sectionId || isNaN(myIndex)) return;

            // Find the immediate neighbor (the other .editable-block
            // whose order_index is exactly one away, in the requested
            // direction) — a simple two-row swap, not a full shift.
            const targetIndex = direction === 'up' ? myIndex - 1 : myIndex + 1;
            let neighbor = null;
            document.querySelectorAll('.editable-block[data-order-index]').forEach(function (other) {
                if (parseInt(other.dataset.orderIndex, 10) === targetIndex) {
                    neighbor = other;
                }
            });
            if (!neighbor) return;

            const order = [
                { id: sectionId, order_index: targetIndex },
                { id: neighbor.dataset.sectionId, order_index: myIndex }
            ];

            const data = new FormData();
            data.append('order', JSON.stringify(order));
            fetch('/page/section/reorder', {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.json())
                .then(function (json) {
                    if (json.success) {
                        window.location.reload();
                    } else {
                        showBlockFeedback(block, 'Fehler beim Verschieben', 'error');
                    }
                })
                .catch(function () {
                    showBlockFeedback(block, 'Verbindungsfehler', 'error');
                });
        }

        block.querySelector('.btn-move-up')?.addEventListener('click', function (e) {
            e.stopPropagation();
            moveSection('up');
        });

        block.querySelector('.btn-move-down')?.addEventListener('click', function (e) {
            e.stopPropagation();
            moveSection('down');
        });
    });

    // to activade edit mode of new section by default without needing to click the pencil
    if (sessionStorage.getItem('new_section')) {
        const id = sessionStorage.getItem('new_section');
        sessionStorage.removeItem('new_section');
        const target = document.querySelector('.editable-block[data-section-id="' + id + '"]');
        if (target) doActivateBlock(target);
    }

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
                            // Derive base path from current URL — works for
                            // /veranstaltungen/, /kuenstlerinnen/, /team/ etc.
                            const parts = window.location.pathname.split('/').filter(Boolean);
                            const base = parts.length >= 1 ? '/' + parts[0] + '/' : '/';
                            history.replaceState(null, '', base + json.slug);
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

    // ── Venue edit row ────────────────────────────────────────
    document.querySelectorAll('.venue-edit-row').forEach(function (row) {
        const pencilBtn = row.querySelector('.venue-pencil-btn');
        const cancelBtn = row.querySelector('.venue-cancel-btn');
        const changeBtn = row.querySelector('[data-action="open-venue-modal"]');
        const removeBtn = row.querySelector('[data-action="remove-venue"]');
        const eventId = row.dataset.entityId;
        const feedback = row.querySelector('.entity-feedback');

        function showFeedback(msg, type) {
            if (!feedback) return;
            feedback.textContent = msg;
            feedback.className = 'entity-feedback entity-feedback--' + type;
            setTimeout(function () { feedback.textContent = ''; }, 3000);
        }

        pencilBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            row.classList.add('editing');
            document.body.classList.add('is-editing');
        });

        cancelBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            row.classList.remove('editing');
            document.body.classList.remove('is-editing');
        });

        function saveVenue(venueId) {
            const data = new FormData();
            data.append('venue_id', venueId);
            fetch('/events/' + eventId + '/save', {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.json())
                .then(function (json) {
                    if (json.success) {
                        showFeedback('Gespeichert ✓', 'success');
                        window.location.reload();
                    } else {
                        showFeedback('Fehler', 'error');
                    }
                })
                .catch(function () { showFeedback('Verbindungsfehler', 'error'); });
        }

        changeBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            openVenueModal('search', function (venue) {
                saveVenue(venue.id);
            });
        });

        removeBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            openConfirmModal({
                title: 'Ort entfernen',
                message: 'Diesen Veranstaltungsort wirklich entfernen?',
                confirmLabel: 'Entfernen',
                onConfirm: function () {
                    saveVenue('');
                }
            });
        });

        row.querySelector('[data-action="edit-venue"]')?.addEventListener('click', function (e) {
            e.stopPropagation();
            const btn = e.currentTarget;
            openVenueModal('edit', {
                id: btn.dataset.venueId,
                name: btn.dataset.venueName,
                street: btn.dataset.venueStreet,
                postcode: btn.dataset.venuePostcode,
                city: btn.dataset.venueCity,
                country: btn.dataset.venueCountry,
                map_url: btn.dataset.venueMapUrl,
                website_url: btn.dataset.venueWebsiteUrl,
            });
        });
    });

    // ── Venue modal ───────────────────────────────────────────
    function openVenueModal(mode, payload) {
        const modal = document.getElementById('venueModal');
        const title = document.getElementById('venueModalTitle');
        const tabs = document.getElementById('venueModalTabs');
        const nameInput = document.getElementById('venueModalName');
        const addBtn = document.getElementById('venueModalAdd');
        const saveBtn = document.getElementById('venueModalSave');
        if (!modal) return;

        // Hide all panels
        modal.querySelectorAll('.venue-modal-panel').forEach(p => p.style.display = 'none');

        if (mode === 'edit') {
            // Edit mode — pre-filled form, no tabs
            const venue = payload;
            tabs.style.display = 'none';
            title.textContent = 'Venue bearbeiten';

            document.getElementById('venueModalEditId').value = venue.id || '';
            document.getElementById('venueModalEditName').value = venue.name || '';
            document.getElementById('venueModalEditStreet').value = venue.street || '';
            document.getElementById('venueModalEditPostcode').value = venue.postcode || '';
            document.getElementById('venueModalEditCity').value = venue.city || '';
            document.getElementById('venueModalEditCountry').value = venue.country || '';
            document.getElementById('venueModalEditMapUrl').value = venue.map_url || '';
            document.getElementById('venueModalEditWebsiteUrl').value = venue.website_url || '';

            modal.querySelector('[data-panel="venue-edit"]').style.display = 'block';

            saveBtn.onclick = function () {
                const venueId = document.getElementById('venueModalEditId').value;
                const fields = {
                    name: document.getElementById('venueModalEditName').value.trim(),
                    street: document.getElementById('venueModalEditStreet').value.trim(),
                    postcode: document.getElementById('venueModalEditPostcode').value.trim(),
                    city: document.getElementById('venueModalEditCity').value.trim(),
                    country: document.getElementById('venueModalEditCountry').value.trim(),
                    map_url: document.getElementById('venueModalEditMapUrl').value.trim(),
                    website_url: document.getElementById('venueModalEditWebsiteUrl').value.trim(),
                };

                // Save each field sequentially
                const saves = Object.entries(fields).map(function ([field, value]) {
                    const data = new FormData();
                    data.append(field, value);
                    return fetch('/venues/' + venueId + '/save', {
                        method: 'POST',
                        body: data,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(r => r.json());
                });

                Promise.all(saves).then(function () {
                    closeVenueModal();
                    window.location.reload();
                }).catch(function () { alert('Verbindungsfehler.'); });
            };

        } else {
            // Search mode — search + neue venue tabs
            tabs.style.display = 'flex';
            title.textContent = 'Veranstaltungsort';

            // Reset search tab
            const searchInput = document.getElementById('venueModalSearch');
            const results = document.getElementById('venueModalResults');
            searchInput.value = '';
            results.innerHTML = '';
            nameInput.value = '';
            document.getElementById('venueModalStreet').value = '';
            document.getElementById('venueModalPostcode').value = '';
            document.getElementById('venueModalCity').value = '';
            document.getElementById('venueModalCountry').value = 'Österreich';
            document.getElementById('venueModalMapUrl').value = '';
            document.getElementById('venueModalWebsiteUrl').value = '';
            addBtn.disabled = true;

            // Activate search tab
            modal.querySelectorAll('.ows-modal-tab').forEach(t => t.classList.remove('ows-modal-tab--active'));
            modal.querySelector('[data-tab="venue-search"]').classList.add('ows-modal-tab--active');
            modal.querySelector('[data-panel="venue-search"]').style.display = 'block';

            const onSelect = payload;

            fetchVenues('');

            function fetchVenues(query) {
                fetch('/venues/search?q=' + encodeURIComponent(query), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        results.innerHTML = '';
                        if (!json.success || !json.venues.length) {
                            results.innerHTML = '<p class="text-muted p-2">Keine Venues gefunden.</p>';
                            return;
                        }
                        json.venues.forEach(function (venue) {
                            const item = document.createElement('div');
                            item.className = 'attach-entity-modal-result-item';
                            item.innerHTML = '<strong>' + venue.name + '</strong>'
                                + (venue.address ? '<small class="d-block text-muted">' + venue.address + '</small>' : '');
                            item.addEventListener('click', function () {
                                closeVenueModal();
                                onSelect(venue);
                            });
                            results.appendChild(item);
                        });
                    });
            }

            searchInput.oninput = function () { fetchVenues(searchInput.value.trim()); };

            // Tab switching
            modal.querySelectorAll('.ows-modal-tab').forEach(function (tab) {
                tab.onclick = function () {
                    modal.querySelectorAll('.ows-modal-tab').forEach(t => t.classList.remove('ows-modal-tab--active'));
                    tab.classList.add('ows-modal-tab--active');
                    modal.querySelectorAll('.venue-modal-panel').forEach(p => p.style.display = 'none');
                    modal.querySelector('[data-panel="' + tab.dataset.tab + '"]').style.display = 'block';
                };
            });

            nameInput.oninput = function () {
                addBtn.disabled = !nameInput.value.trim();
            };

            addBtn.onclick = function () {
                const data = new FormData();
                data.append('name', nameInput.value.trim());
                data.append('street', document.getElementById('venueModalStreet').value.trim());
                data.append('postcode', document.getElementById('venueModalPostcode').value.trim());
                data.append('city', document.getElementById('venueModalCity').value.trim());
                data.append('country', document.getElementById('venueModalCountry').value.trim());
                data.append('map_url', document.getElementById('venueModalMapUrl').value.trim());
                data.append('website_url', document.getElementById('venueModalWebsiteUrl').value.trim());

                fetch('/venues/add', {
                    method: 'POST',
                    body: data,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        if (json.success) {
                            closeVenueModal();
                            onSelect(json);
                        }
                    })
                    .catch(function () { alert('Verbindungsfehler.'); });
            };
        }

        modal.style.display = 'flex';
        modal.querySelector('.venue-modal-close').onclick = closeVenueModal;
        modal.onclick = function (e) { if (e.target === modal) closeVenueModal(); };
    }

    function closeVenueModal() {
        const modal = document.getElementById('venueModal');
        if (modal) modal.style.display = 'none';
    }

    // ── Venue URL test links ──────────────────────────────────
    function bindVenueTestLink(inputId, linkId) {
        const input = document.getElementById(inputId);
        const link = document.getElementById(linkId);
        if (!input || !link) return;
        input.addEventListener('input', function () {
            const val = input.value.trim();
            if (val && val.startsWith('http')) {
                link.href = val;
                link.style.display = 'inline-flex';
            } else {
                link.style.display = 'none';
            }
        });
    }

    bindVenueTestLink('venueModalMapUrl', 'venueModalMapUrlTest');
    bindVenueTestLink('venueModalWebsiteUrl', 'venueModalWebsiteUrlTest');
    bindVenueTestLink('venueModalEditMapUrl', 'venueModalEditMapUrlTest');
    bindVenueTestLink('venueModalEditWebsiteUrl', 'venueModalEditWebsiteUrlTest');

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
                            '<a href="/kuenstlerinnen/' + (json.slug || '#') + '">' +
                            selectedText +
                            (json.field ? ' · <span class="participant-field">' + json.field + '</span>' : '') +
                            '</a>';
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
                openConfirmModal({
                    title: 'Mitwirkende:n entfernen',
                    message: 'Diese:n Mitwirkende:n wirklich von der Veranstaltung entfernen?',
                    confirmLabel: 'Entfernen',
                    onConfirm: function () {
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
                    }
                });
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
            // check requires a real hostname shape (one or more dot-
            // separated labels) — accepts a single, bare label too
            // (e.g. "localhost"), since that's a syntactically real,
            // legitimate hostname, just not a public one. The actual
            // gap this closes is garbage text that doesn't even look
            // like a hostname at all.
            const domainShapePattern = /^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)*$/;
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
                                refreshLinksList(row);
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
                    refreshLinksList(row);
                    showEntityFeedback(row, 'Hinzugefügt ✓', 'success');
                })
                .catch(function () {
                    showEntityFeedback(row, 'Verbindungsfehler', 'error');
                });
        }

        function refreshLinksList(row) {
            const container = row.querySelector('.links-list-container');
            if (!container) return;

            fetch('/urls/fragment?entity_type=' + encodeURIComponent(entityType) + '&entity_id=' + encodeURIComponent(entityId), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.text())
                .then(function (html) {
                    container.innerHTML = html;
                    container.querySelectorAll('[data-action="edit-entity-url"]').forEach(function (btn) {
                        bindEditUrl(btn, row);
                    });
                    container.querySelectorAll('[data-action="remove-entity-url"]').forEach(function (btn) {
                        bindRemoveUrl(btn, row);
                    });
                })
                .catch(function () {
                    showEntityFeedback(row, 'Verbindungsfehler', 'error');
                });
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
                                refreshLinksList(row);
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
                        refreshLinksList(row);
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
                            const fragmentUrl = row.dataset.fragmentUrl;
                            if (fragmentUrl) {
                                // Promo / profile — re-fetch the declared fragment
                                // and swap content in place. The row owns the URL
                                // via data-fragment-url — JS never branches on stage.
                                const content = row.querySelector('.media-promo-content');
                                if (content) {
                                    fetch(fragmentUrl, {
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
                                // Gallery — no fragment endpoint; append new item
                                // directly and wire its delete button.
                                const grid = row.querySelector('.media-gallery-grid');
                                if (grid) {
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
                openConfirmModal({
                    title: 'Bild löschen',
                    message: 'Dieses Bild wirklich löschen?',
                    confirmLabel: 'Löschen',
                    onConfirm: function () {
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

                                // Promo / profile — re-fetch the declared fragment
                                // and swap content in place. Row owns the URL via
                                // data-fragment-url — no stage logic needed here.
                                const fragmentUrl = row.dataset.fragmentUrl;
                                const content = row.querySelector('.media-promo-content');
                                if (fragmentUrl && content) {
                                    fetch(fragmentUrl, {
                                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                    })
                                        .then(res => res.text())
                                        .then(function (html) {
                                            content.innerHTML = html;
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
                    }
                });
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
                ? 'Dieses Foto wirklich löschen?'
                : count + ' Fotos wirklich löschen?';

            openConfirmModal({
                title: count === 1 ? 'Foto löschen' : 'Fotos löschen',
                message: confirmMsg,
                confirmLabel: 'Löschen',
                onConfirm: function () {
                    const items = selected.map(cb => cb.closest('.gallery-item')).filter(Boolean);
                    let index = 0;

                    function deleteNext() {
                        if (index >= items.length) {
                            const grid = row.querySelector('.media-gallery-grid');
                            if (grid && grid.querySelectorAll('.gallery-item').length === 0) {
                                grid.innerHTML =
                                    '<div class="col-12"><p class="text-muted p-2">' +
                                    '<i class="ti ti-photo-off"></i> ' +
                                    'Noch keine Galeriebilder' +
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
                }
            });
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

    // ── Publish event ─────────────────────────────────────────
    document.querySelector('[data-action="publish-event"]')?.addEventListener('click', function (e) {
        const eventId = e.currentTarget.dataset.eventId;
        openConfirmModal({
            title: 'Veranstaltung veröffentlichen',
            message: 'Diese Veranstaltung wird auf der öffentlichen Website angezeigt. Sicher veröffentlichen?',
            confirmLabel: 'Veröffentlichen',
            onConfirm: function () {
                fetch('/events/' + eventId + '/publish', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        if (json.success) {
                            window.location.reload();
                        } else {
                            alert('Fehler beim Veröffentlichen.');
                        }
                    })
                    .catch(function () { alert('Verbindungsfehler.'); });
            }
        });
    });

    // ── Unpublish event ───────────────────────────────────────
    document.querySelector('[data-action="unpublish-event"]')?.addEventListener('click', function (e) {
        const eventId = e.currentTarget.dataset.eventId;
        openConfirmModal({
            title: 'Als Entwurf speichern',
            message: 'Diese Veranstaltung wird von der öffentlichen Website entfernt und als Entwurf gespeichert.',
            confirmLabel: 'Als Entwurf',
            onConfirm: function () {
                fetch('/events/' + eventId + '/unpublish', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        if (json.success) {
                            window.location.reload();
                        } else {
                            alert('Fehler.');
                        }
                    })
                    .catch(function () { alert('Verbindungsfehler.'); });
            }
        });
    });

    // ── Cancel event ──────────────────────────────────────────
    document.querySelector('[data-action="cancel-event"]')?.addEventListener('click', function (e) {
        const eventId = e.currentTarget.dataset.eventId;
        openConfirmModal({
            title: 'Veranstaltung absagen',
            message: 'Diese Veranstaltung wird abgesagt und von der öffentlichen Website entfernt. Die Daten bleiben erhalten.',
            confirmLabel: 'Absagen',
            onConfirm: function () {
                fetch('/events/' + eventId + '/cancel', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        if (json.success) {
                            window.location.reload();
                        } else {
                            alert('Fehler beim Absagen.');
                        }
                    })
                    .catch(function () { alert('Verbindungsfehler.'); });
            }
        });
    });

    // ── Delete event ──────────────────────────────────────────
    document.querySelector('[data-action="delete-event"]')?.addEventListener('click', function (e) {
        const btn = e.currentTarget;
        const eventId = btn.dataset.eventId;

        openConfirmModal({
            title: 'Veranstaltung löschen',
            message: 'Dieser Entwurf wird dauerhaft gelöscht.',
            confirmLabel: 'Endgültig löschen',
            onConfirm: function () {
                fetch('/events/' + eventId + '/delete', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        if (json.success) {
                            window.location.href = '/veranstaltungen';
                        } else {
                            alert(json.error || 'Löschen fehlgeschlagen.');
                        }
                    })
                    .catch(function () {
                        alert('Verbindungsfehler.');
                    });
            }
        });
    });

    // ── New participant ───────────────────────────────────────
    document.querySelector('[data-action="new-participant"]')?.addEventListener('click', function (e) {
        e.preventDefault();
        fetch('/participants/add', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(function (json) {
                if (json.success && json.slug) {
                    window.location.href = '/kuenstlerinnen/' + json.slug;
                } else {
                    alert('Fehler beim Erstellen der Künstler:in.');
                }
            })
            .catch(function () {
                alert('Verbindungsfehler.');
            });
    });

    // ── Delete participant ────────────────────────────────────
    document.querySelector('[data-action="delete-participant"]')?.addEventListener('click', function (e) {
        const btn = e.currentTarget;
        const participantId = btn.dataset.participantId;

        openConfirmModal({
            title: 'Künstler:in löschen',
            message: 'Diese:r Künstler:in wird dauerhaft gelöscht. Alle Verknüpfungen zu Veranstaltungen werden ebenfalls entfernt.',
            confirmLabel: 'Endgültig löschen',
            onConfirm: function () {
                fetch('/participants/' + participantId + '/delete', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        if (json.success) {
                            window.location.href = '/kuenstlerinnen';
                        } else {
                            alert('Löschen fehlgeschlagen.');
                        }
                    })
                    .catch(function () {
                        alert('Verbindungsfehler.');
                    });
            }
        });
    });


    // ── Publish participant ───────────────────────────────────
    document.querySelector('[data-action="publish-participant"]')?.addEventListener('click', function (e) {
        const participantId = e.currentTarget.dataset.participantId;
        openConfirmModal({
            title: 'Künstler:in veröffentlichen',
            message: 'Diese:r Künstler:in wird auf der öffentlichen Website angezeigt. Sicher veröffentlichen?',
            confirmLabel: 'Veröffentlichen',
            onConfirm: function () {
                fetch('/participants/' + participantId + '/publish', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        if (json.success) { window.location.reload(); }
                        else { alert('Fehler beim Veröffentlichen.'); }
                    })
                    .catch(function () { alert('Verbindungsfehler.'); });
            }
        });
    });

    // ── Unpublish participant ─────────────────────────────────
    document.querySelector('[data-action="unpublish-participant"]')?.addEventListener('click', function (e) {
        const participantId = e.currentTarget.dataset.participantId;
        openConfirmModal({
            title: 'Als Entwurf speichern',
            message: 'Diese:r Künstler:in wird von der öffentlichen Website entfernt.',
            confirmLabel: 'Als Entwurf',
            onConfirm: function () {
                fetch('/participants/' + participantId + '/unpublish', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        if (json.success) { window.location.reload(); }
                        else { alert('Fehler.'); }
                    })
                    .catch(function () { alert('Verbindungsfehler.'); });
            }
        });
    });

    // ── New team member ───────────────────────────────────────
    document.querySelector('[data-action="new-team"]')?.addEventListener('click', function (e) {
        e.preventDefault();
        fetch('/team/add', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(function (json) {
                if (json.success && json.slug) {
                    window.location.href = '/team/' + json.slug;
                } else {
                    alert('Fehler beim Erstellen des Teammitglieds.');
                }
            })
            .catch(function () {
                alert('Verbindungsfehler.');
            });
    });

    // ── Delete team member ────────────────────────────────────
    document.querySelector('[data-action="delete-team"]')?.addEventListener('click', function (e) {
        const btn = e.currentTarget;
        const teamId = btn.dataset.teamId;

        openConfirmModal({
            title: 'Teammitglied löschen',
            message: 'Dieses Teammitglied wird aus der öffentlichen Ansicht entfernt. Der Datensatz bleibt für historische Zwecke erhalten.',
            confirmLabel: 'Löschen',
            onConfirm: function () {
                fetch('/team/' + teamId + '/delete', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        if (json.success) {
                            window.location.href = '/team';
                        } else {
                            alert('Löschen fehlgeschlagen.');
                        }
                    })
                    .catch(function () {
                        alert('Verbindungsfehler.');
                    });
            }
        });
    });


    // ── Publish team member ───────────────────────────────────
    document.querySelector('[data-action="publish-team"]')?.addEventListener('click', function (e) {
        const teamId = e.currentTarget.dataset.teamId;
        openConfirmModal({
            title: 'Teammitglied veröffentlichen',
            message: 'Dieses Teammitglied wird auf der öffentlichen Website angezeigt. Sicher veröffentlichen?',
            confirmLabel: 'Veröffentlichen',
            onConfirm: function () {
                fetch('/team/' + teamId + '/publish', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        if (json.success) { window.location.reload(); }
                        else { alert('Fehler beim Veröffentlichen.'); }
                    })
                    .catch(function () { alert('Verbindungsfehler.'); });
            }
        });
    });

    // ── Unpublish team member ─────────────────────────────────
    document.querySelector('[data-action="unpublish-team"]')?.addEventListener('click', function (e) {
        const teamId = e.currentTarget.dataset.teamId;
        openConfirmModal({
            title: 'Als Entwurf speichern',
            message: 'Dieses Teammitglied wird von der öffentlichen Website entfernt.',
            confirmLabel: 'Als Entwurf',
            onConfirm: function () {
                fetch('/team/' + teamId + '/unpublish', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        if (json.success) { window.location.reload(); }
                        else { alert('Fehler.'); }
                    })
                    .catch(function () { alert('Verbindungsfehler.'); });
            }
        });
    });

    // ── Section CTA buttons (free sections) ───────────────────
    // Reuses the SHARED attach-entity-modal shell — exactly the
    // same component already powering the Links picker — rather
    // than a separate, parallel modal. The only thing genuinely
    // specific to CTAs is the 'page' tab and the cta_label field;
    // everything else (search, real type select, validation,
    // disabled-until-valid logic) comes from the shell for free.
    document.querySelectorAll('.section-cta-row').forEach(function (row) {
        const sectionId = row.dataset.entityId;
        let ctaTypeOptions = null;

        function refreshCtaRow() {
            fetch('/urls/section-cta-fragment?section_id=' + encodeURIComponent(sectionId), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.text())
                .then(function (html) {
                    row.innerHTML = html;
                })
                .catch(function () {
                    showEntityFeedback(row, 'Verbindungsfehler', 'error');
                });
        }

        function withTypeOptions(callback) {
            if (ctaTypeOptions) {
                callback(ctaTypeOptions);
                return;
            }
            fetch('/urls/types', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.json())
                .then(function (json) {
                    ctaTypeOptions = (json.success ? json.types : []).map(function (t) {
                        return { value: t.id, label: t.label };
                    });
                    callback(ctaTypeOptions);
                });
        }

        // Reuses the SAME validation shape the Links picker's
        // validateAndPreview() already produces — error/warning/
        // preview — so a CTA's url gets the exact same real,
        // type-aware checking, never a weaker, CTA-only version.
        function ctaPreviewFn(typeOptions) {
            return function (values) {
                const typeId = values.url_type_id;
                const rawUrl = (values.url || '').trim();
                if (!rawUrl) return null;

                const typeOpt = typeOptions.find(function (t) { return String(t.value) === String(typeId); });
                const typeLabel = (typeOpt?.label || '').toLowerCase();

                let normalized = rawUrl.replace(/^http:\/\//i, 'https://');
                if (!/^https?:\/\//i.test(normalized)) normalized = 'https://' + normalized;
                normalized = normalized.replace(/\/$/, '');

                try {
                    const parsed = new URL(normalized);
                    const domainShape = /^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)*$/;
                    if (!domainShape.test(parsed.hostname.toLowerCase())) {
                        return { error: 'Bitte eine gültige URL eingeben (z. B. example.com).' };
                    }
                } catch (e) {
                    return { error: 'Bitte eine gültige URL eingeben.' };
                }

                return { preview: normalized };
            };
        }

        row.addEventListener('click', function (e) {
            const addBtn = e.target.closest('[data-action="add-section-cta"]');
            const editBtn = e.target.closest('[data-action="edit-section-cta"]');
            const removeBtn = e.target.closest('[data-action="remove-section-cta"]');

            if (addBtn) {
                withTypeOptions(function (typeOptions) {
                    openAttachEntityModal({
                        title: 'Button hinzufügen',
                        tabs: ['search', 'new', 'page'],
                        searchFillsNewTab: true,
                        searchEndpoint: '/urls/search',
                        searchPlaceholder: 'Vorhandene Links durchsuchen...',
                        addEndpoint: '/urls/add',
                        addFields: [
                            { name: 'url_type_id', label: 'Typ', type: 'select', required: true, options: typeOptions },
                            { name: 'url', label: 'URL', type: 'text', required: true, placeholder: 'https://...' },
                            { name: 'cta_label', label: 'Button-Text', type: 'text', required: true, placeholder: 'z. B. Jetzt Mitglied werden' }
                        ],
                        pageFields: [
                            { name: 'cta_label', label: 'Button-Text', type: 'text', required: true, placeholder: 'z. B. Jetzt Mitglied werden' }
                        ],
                        namedPagesEndpoint: '/urls/named-pages',
                        pageAddEndpoint: '/urls/add-internal-page',
                        buildPageUrl: function (path) { return window.location.origin + path; },
                        previewFn: ctaPreviewFn(typeOptions),
                        extraAddParams: { entity_type: 'section', entity_id: sectionId },
                        onSelected: refreshCtaRow
                    });
                });
            } else if (editBtn) {
                withTypeOptions(function (typeOptions) {
                    const currentUrl = editBtn.dataset.urlValue;
                    let isInternalPage = false;
                    let currentPagePath = '';
                    try {
                        const parsed = new URL(currentUrl);
                        if (parsed.origin === window.location.origin) {
                            isInternalPage = true;
                            currentPagePath = parsed.pathname;
                        }
                    } catch (e) {
                        // Not a valid absolute URL at all — definitely
                        // not an internal-page link, fall through to
                        // the ordinary external-link edit form.
                    }

                    if (isInternalPage) {
                        openAttachEntityModal({
                            mode: 'edit',
                            editPanel: 'page',
                            title: 'Button bearbeiten (interne Seite)',
                            confirmLabel: 'Speichern',
                            addEndpoint: '/urls/' + editBtn.dataset.urlId + '/save',
                            namedPagesEndpoint: '/urls/named-pages',
                            buildPageUrl: function (path) { return window.location.origin + path; },
                            currentPagePath: currentPagePath,
                            pageFields: [
                                { name: 'cta_label', label: 'Button-Text', type: 'text', required: true, placeholder: 'z. B. Jetzt Mitglied werden', value: editBtn.dataset.urlLabel }
                            ],
                            extraAddParams: {
                                entity_type: 'section',
                                entity_id: sectionId,
                                // save() requires url_type_id explicitly —
                                // unlike addInternalPage(), it has no
                                // auto-derivation, so the already-known
                                // Website type id is supplied directly.
                                url_type_id: (typeOptions.find(function (t) { return (t.label || '').toLowerCase() === 'website'; }) || {}).value
                            },
                            onSelected: refreshCtaRow
                        });
                        return;
                    }

                    openAttachEntityModal({
                        mode: 'edit',
                        title: 'Button bearbeiten (externer Link)',
                        confirmLabel: 'Speichern',
                        addEndpoint: '/urls/' + editBtn.dataset.urlId + '/save',
                        addFields: [
                            { name: 'url_type_id', label: 'Typ', type: 'select', required: true, options: typeOptions, value: editBtn.dataset.urlTypeId },
                            { name: 'url', label: 'URL', type: 'text', required: true, placeholder: 'https://...', value: editBtn.dataset.urlValue },
                            { name: 'cta_label', label: 'Button-Text', type: 'text', required: true, placeholder: 'z. B. Jetzt Mitglied werden', value: editBtn.dataset.urlLabel }
                        ],
                        previewFn: ctaPreviewFn(typeOptions),
                        extraAddParams: { entity_type: 'section', entity_id: sectionId },
                        onSelected: refreshCtaRow
                    });
                });
            } else if (removeBtn) {
                performCtaUnlink(removeBtn.dataset.urlId, false);
            }
        });

        function performCtaUnlink(urlId, confirmed) {
            const data = new FormData();
            data.append('entity_type', 'section');
            data.append('entity_id', sectionId);
            if (confirmed) data.append('confirmed', '1');

            fetch('/urls/' + urlId + '/unlink', {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.json())
                .then(function (json) {
                    if (json.needsConfirmation) {
                        openConfirmModal({
                            title: 'Button entfernen',
                            message: 'Dieser Link ist nirgendwo sonst verknüpft. Beim Entfernen wird er endgültig gelöscht.',
                            confirmLabel: 'Endgültig entfernen',
                            onConfirm: function () {
                                performCtaUnlink(urlId, true);
                            }
                        });
                        return;
                    }
                    refreshCtaRow();
                });
        }
    });

    // ── Add-section triggers ────────────────────────────────────
    // One isolated function handling the whole job in v1 (compute
    // position, shift, create, reload). In v2, THIS function's body
    // becomes the natural place to open a layout/thumbnail picker
    // first — no changes needed to the triggers themselves, their
    // markup, or where they're placed on the page.
    function triggerAddSection(pageKey, afterIndex, beforeIndex, isIntro) {
        let newIndex;
        if (isIntro) {
            // The one reserved slot — always exactly 0, on pages
            // that have this concept at all (listing/fixed-structure
            // pages). Never inferred from afterIndex/beforeIndex,
            // since those alone can't distinguish this from an
            // ordinary first-position trigger on a page with no
            // reserved slot.
            newIndex = 0;
        } else {
            // Ordinary trigger — minimum 1, since order_index=0 is
            // EXCLUSIVELY the reserved intro slot's value; a regular
            // section must never claim it, even when inserting at
            // the very start of a page's free-section sequence.
            newIndex = Math.max(1, (afterIndex === null ? 0 : afterIndex + 1));
        }

        // Find every existing section on THIS page whose order_index
        // is at or above the slot the new section needs to occupy —
        // each of those needs to shift up by one first, so the new
        // section can claim newIndex without colliding.
        const toShift = [];
        document.querySelectorAll('.editable-block[data-order-index]').forEach(function (block) {
            const blockIndex = parseInt(block.dataset.orderIndex, 10);
            if (!isNaN(blockIndex) && blockIndex >= newIndex) {
                toShift.push({ id: parseInt(block.dataset.sectionId, 10), order_index: blockIndex + 1 });
            }
        });

        function createSection() {
            const data = new FormData();
            data.append('page_key', pageKey);
            data.append('order_index', newIndex);
            data.append('content', '{}');

            fetch('/page/section/add', {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.json())
                .then(function (json) {
                    if (json.success) {
                        // to active edit mode of newly created session
                        sessionStorage.setItem('new_section', json.id);
                        window.location.reload();
                    } else {
                        alert('Fehler beim Hinzufügen des Abschnitts.');
                    }
                })
                .catch(function () {
                    alert('Verbindungsfehler.');
                });
        }

        if (toShift.length === 0) {
            createSection();
            return;
        }

        const reorderData = new FormData();
        reorderData.append('order', JSON.stringify(toShift));
        fetch('/page/section/reorder', {
            method: 'POST',
            body: reorderData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(function (json) {
                if (json.success) {
                    createSection();
                } else {
                    alert('Fehler beim Verschieben bestehender Abschnitte.');
                }
            })
            .catch(function () {
                alert('Verbindungsfehler.');
            });
    }

    document.querySelectorAll('[data-action="add-section"]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const pageKey = btn.dataset.pageKey;
            const afterIndex = btn.dataset.afterIndex !== '' ? parseInt(btn.dataset.afterIndex, 10) : null;
            const beforeIndex = btn.dataset.beforeIndex !== '' ? parseInt(btn.dataset.beforeIndex, 10) : null;
            const isIntro = btn.dataset.isIntro === '1';
            triggerAddSection(pageKey, afterIndex, beforeIndex, isIntro);
        });
    });


    // ── Org logo edit rows (Logo + Inline-Logo) ────────────────
    // Same media-edit-row pattern: pencil toggles .editing on the
    // row, CSS shows the in-item edit/trash controls and the
    // upload-wrap only while .editing is active.
    document.querySelectorAll('[data-stage="logo"], [data-stage="inline-logo"]').forEach(function (row) {
        const pencilBtn = row.querySelector('.org-logo-pencil-btn');
        const cancelBtn = row.querySelector('.org-logo-cancel-btn');
        const feedback = row.querySelector('.entity-feedback');
        const orgNameForNav = document.querySelector('h1')?.textContent.trim() ?? '';

        function showFeedback(msg, type) {
            if (!feedback) return;
            feedback.textContent = msg;
            feedback.className = 'entity-feedback entity-feedback--' + type;
            setTimeout(function () { feedback.textContent = ''; }, 3000);
        }

        pencilBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            row.classList.add('editing');
            document.body.classList.add('is-editing');
            pencilBtn.style.display = 'none';
            cancelBtn.style.display = 'inline-flex';
            row.querySelectorAll('.org-logo-item .entity-edit-btn, .org-logo-item .entity-remove-btn')
                .forEach(function (el) { el.style.display = 'inline-flex'; });
            row.querySelectorAll('.org-logo-upload-wrap')
                .forEach(function (el) { el.style.display = 'flex'; });
        });

        cancelBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            row.classList.remove('editing');
            document.body.classList.remove('is-editing');
            pencilBtn.style.display = 'inline-flex';
            cancelBtn.style.display = 'none';
            row.querySelectorAll('.org-logo-item .entity-edit-btn, .org-logo-item .entity-remove-btn')
                .forEach(function (el) { el.style.display = 'none'; });
            row.querySelectorAll('.org-logo-upload-wrap')
                .forEach(function (el) { el.style.display = 'none'; });
        });

        function bindUpload(input) {
            if (input._uploadInitialized) return;
            input._uploadInitialized = true;
            input.addEventListener('change', function () {
                if (!this.files[0]) return;
                const field = this.dataset.field;
                const item = row.querySelector('.org-logo-item[data-field="' + field + '"]');

                const data = new FormData();
                data.append('image', this.files[0]);
                data.append('field', field);
                const adminPath = window.location.pathname.split('/')[1];

                showFeedback('Wird hochgeladen...', 'success');

                fetch('/' + adminPath + '/org/logo/upload', {
                    method: 'POST',
                    body: data,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(function (json) {
                        if (json.success) {
                            item.innerHTML =
                                '<label class="entity-edit-btn border-0" style="cursor:pointer; display:inline-flex;" title="Logo ersetzen">' +
                                '<i class="ti ti-pencil"></i>' +
                                '<input type="file" accept="image/*" class="d-none" data-action="upload-org-logo" data-field="' + field + '">' +
                                '</label>' +
                                '<button class="entity-remove-btn border-0" style="display:inline-flex;" data-action="delete-org-logo" data-field="' + field + '">' +
                                '<i class="ti ti-trash"></i></button>' +
                                '<img src="' + json.url + '" alt="Logo" style="max-height:80px;">';
                            row.querySelector('.org-logo-upload-wrap')?.remove();
                            bindAllControls();
                            showFeedback('Gespeichert ✓', 'success');

                            // Instant navbar update — only inline_logo_url affects the nav
                            if (field === 'inline_logo_url') {
                                const navLink = document.querySelector('.nav-brand-link');
                                if (navLink) {
                                    navLink.innerHTML = '<img class="nav-brand" src="' + json.url + '" alt="">';
                                }
                            }

                            // Instant footer update — logo_url drives the footer logo
                            if (field === 'logo_url') {
                                const footerLogo = document.querySelector('.footer-logo');
                                if (footerLogo) {
                                    footerLogo.querySelector('img').src = json.url;
                                } else {
                                    const footer = document.querySelector('footer');
                                    const copyright = footer?.querySelector('p');
                                    if (footer && copyright) {
                                        const wrap = document.createElement('div');
                                        wrap.className = 'footer-logo margin-top';
                                        wrap.innerHTML = '<img src="' + json.url + '" alt="">';
                                        footer.insertBefore(wrap, copyright);
                                    }
                                }
                            }
                        } else {
                            showFeedback(json.error || 'Fehler', 'error');
                        }
                    })
                    .catch(function () {
                        showFeedback('Verbindungsfehler', 'error');
                    });
            });
        }

        function bindDelete(btn) {
            if (btn._deleteInitialized) return;
            btn._deleteInitialized = true;
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const field = btn.dataset.field;
                const item = row.querySelector('.org-logo-item[data-field="' + field + '"]');

                openConfirmModal({
                    title: 'Logo löschen',
                    message: 'Dieses Logo wirklich löschen?',
                    confirmLabel: 'Löschen',
                    onConfirm: function () {
                        const data = new FormData();
                        data.append('field', field);
                        const adminPath = window.location.pathname.split('/')[1];

                        fetch('/' + adminPath + '/org/logo/delete', {
                            method: 'POST',
                            body: data,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                            .then(res => res.json())
                            .then(function (json) {
                                if (json.success) {
                                    item.innerHTML = '<p class="text-muted mb-0">— kein Logo —</p>';
                                    const label = field === 'logo_url' ? 'Logo hochladen' : 'Inline-Logo hochladen';
                                    const wrap = document.createElement('div');
                                    wrap.className = 'org-logo-upload-wrap p-2';
                                    wrap.style.display = 'flex';
                                    wrap.innerHTML =
                                        '<label class="entity-edit-btn" style="cursor:pointer;">' +
                                        '<i class="ti ti-photo-plus"></i> ' + label +
                                        '<input type="file" accept="image/*" class="d-none" data-action="upload-org-logo" data-field="' + field + '">' +
                                        '</label>';
                                    row.appendChild(wrap);
                                    bindAllControls();
                                    showFeedback('Gelöscht ✓', 'success');

                                    // Instant navbar update — revert to org name text
                                    if (field === 'inline_logo_url') {
                                        const navLink = document.querySelector('.nav-brand-link');
                                        if (navLink) {
                                            navLink.innerHTML = '<span class="nav-brand-text">' + orgNameForNav + '</span>';
                                        }
                                    }

                                    // Instant footer removal — logo_url drives the footer logo
                                    if (field === 'logo_url') {
                                        document.querySelector('.footer-logo')?.remove();
                                    }
                                } else {
                                    showFeedback(json.error || 'Fehler', 'error');
                                }
                            })
                            .catch(function () {
                                showFeedback('Verbindungsfehler', 'error');
                            });
                    }
                });
            });
        }

        function bindAllControls() {
            row.querySelectorAll('[data-action="upload-org-logo"]').forEach(bindUpload);
            row.querySelectorAll('[data-action="delete-org-logo"]').forEach(bindDelete);
        }

        bindAllControls();
    });


    // ── Role select — live preview + Sonstiges free-text fallback ──
    // Purely additive to the existing entity-select-row behaviour
    // above (unchanged) — this only adds two things specific to the
    // role field: instant display update on change (not just after
    // save), and revealing a text input when "Sonstiges" is chosen.
    document.querySelectorAll('.role-select').forEach(function (select) {
        const row = select.closest('.entity-select-row');
        const display = row?.querySelector('.entity-select-display');
        const customInput = row?.querySelector('.role-custom-input');

        function syncCustomVisibility() {
            const isCustom = select.value === '__custom__';
            if (customInput) customInput.style.display = isCustom ? 'block' : 'none';
        }

        function updatePreview() {
            if (!display) return;
            if (select.value === '__custom__') {
                display.textContent = customInput?.value.trim() || '—';
            } else {
                display.textContent = select.options[select.selectedIndex]?.text ?? '—';
            }
        }

        syncCustomVisibility();

        select.addEventListener('change', function () {
            syncCustomVisibility();
            updatePreview();
            if (select.value === '__custom__') customInput?.focus();
        });

        customInput?.addEventListener('input', updatePreview);

        // Override this row's save button to send the custom text
        // when Sonstiges is active, instead of the literal "__custom__"
        // sentinel value the <select> itself would otherwise submit.
        const saveBtn = row?.querySelector('.entity-save-btn');
        saveBtn?.addEventListener('click', function () {
            if (select.value === '__custom__' && customInput) {
                const typed = customInput.value.trim();
                if (!typed) return;
                select.value = typed;
                const exists = Array.from(select.options).some(function (o) { return o.value === typed; });
                if (!exists) {
                    const opt = document.createElement('option');
                    opt.value = typed;
                    opt.textContent = typed;
                    opt.selected = true;
                    select.appendChild(opt);
                }
            }
        }, true);// capture phase — runs before entity-select-row's own save listener
    });

    // ── Team staff grid reorder ───────────────────────────────────
    // Native HTML5 drag-and-drop. 
    // order_index 0 (legal rep) lives in its own locked row above —
    // never appears in .team-staff-grid, never touched here.
    const staffRow = document.querySelector('.team-staff-edit-row');
    if (staffRow) {
        const pencilBtn = staffRow.querySelector('.team-staff-pencil-btn');
        const saveBtn = staffRow.querySelector('.team-staff-save-btn');
        const cancelBtn = staffRow.querySelector('.team-staff-cancel-btn');
        const grid = staffRow.querySelector('.team-staff-grid');
        const saveUrl = staffRow.dataset.saveUrl;
        let originalOrder = [];
        let dragSrc = null;

        function getCards() {
            return Array.from(grid.querySelectorAll('.team-staff-card'));
        }

        function snapshotOrder() {
            originalOrder = getCards().map(function (card) {
                return card.dataset.memberId;
            });
        }

        function restoreOrder() {
            originalOrder.forEach(function (memberId) {
                const card = grid.querySelector('.team-staff-card[data-member-id="' + memberId + '"]');
                if (card) grid.appendChild(card);
            });
        }

        function onDragStart(e) {
            dragSrc = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        }

        function onDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            this.classList.add('drag-over');
        }

        function onDragLeave() {
            this.classList.remove('drag-over');
        }

        function onDrop(e) {
            e.stopPropagation();
            this.classList.remove('drag-over');
            if (dragSrc === this) return;
            const cards = getCards();
            const srcIdx = cards.indexOf(dragSrc);
            const tgtIdx = cards.indexOf(this);
            if (srcIdx < tgtIdx) {
                grid.insertBefore(dragSrc, this.nextSibling);
            } else {
                grid.insertBefore(dragSrc, this);
            }
        }

        function onDragEnd() {
            this.classList.remove('dragging');
            getCards().forEach(function (card) {
                card.classList.remove('drag-over');
            });
        }

        function enableDrag() {
            getCards().forEach(function (card) {
                card.setAttribute('draggable', 'true');
                card.addEventListener('dragstart', onDragStart);
                card.addEventListener('dragover', onDragOver);
                card.addEventListener('dragleave', onDragLeave);
                card.addEventListener('drop', onDrop);
                card.addEventListener('dragend', onDragEnd);
            });
        }

        function disableDrag() {
            getCards().forEach(function (card) {
                card.setAttribute('draggable', 'false');
                card.removeEventListener('dragstart', onDragStart);
                card.removeEventListener('dragover', onDragOver);
                card.removeEventListener('dragleave', onDragLeave);
                card.removeEventListener('drop', onDrop);
                card.removeEventListener('dragend', onDragEnd);
            });
        }

        pencilBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            snapshotOrder();
            staffRow.classList.add('editing');
            document.body.classList.add('is-editing');
            enableDrag();
        });

        cancelBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            restoreOrder();
            disableDrag();
            staffRow.classList.remove('editing');
            document.body.classList.remove('is-editing');
        });

        saveBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            const order = getCards().map(function (card, index) {
                return { id: parseInt(card.dataset.memberId, 10), order_index: index + 1 };
            });
            const data = new FormData();
            data.append('order', JSON.stringify(order));
            saveBtn.disabled = true;

            fetch(saveUrl, {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.json())
                .then(function (json) {
                    if (json.success) {
                        disableDrag();
                        staffRow.classList.remove('editing');
                        document.body.classList.remove('is-editing');
                        showEntityFeedback(staffRow, 'Gespeichert ✓', 'success');
                    } else {
                        showEntityFeedback(staffRow, 'Fehler', 'error');
                    }
                    saveBtn.disabled = false;
                })
                .catch(function () {
                    showEntityFeedback(staffRow, 'Verbindungsfehler', 'error');
                    saveBtn.disabled = false;
                });
        });
    }

    // ── Warn on page leave ────────────────────────────────────
    window.addEventListener('beforeunload', function (e) {
        if (hasUnsaved) { e.preventDefault(); e.returnValue = ''; }
    });

});