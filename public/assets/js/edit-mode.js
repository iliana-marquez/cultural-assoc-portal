/**
 * edit-mode.js
 * kulturCMS — Inline edit mode for sections and entity records.
 *
 * Behaviours:
 *   - Edit button visible on all blocks when editor logged in
 *   - Click Edit → activates block (border, controls, contenteditable)
 *   - Click Save → AJAX POST → saves to DB → deactivates
 *   - Click Cancel → discards changes → deactivates
 *   - One active block at a time — warns on unsaved changes
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── State ─────────────────────────────────────────────────
    let activeBlock = null;
    let hasUnsaved = false;
    let originalValues = {};

    // ── Block activation ──────────────────────────────────────

    function activateBlock(block) {
        // Warn if another block has unsaved changes
        if (activeBlock && activeBlock !== block && hasUnsaved) {
            if (!confirm('Ungespeicherte Änderungen verwerfen?')) return;
            cancelBlock(activeBlock);
        }

        // Deactivate previous
        if (activeBlock && activeBlock !== block) {
            deactivateBlock(activeBlock);
        }

        activeBlock = block;
        block.classList.add('editing');

        // Store original values for cancel
        originalValues = {};
        block.querySelectorAll('[data-field]').forEach(function (el) {
            originalValues[el.dataset.field] = el.innerHTML;
            el.contentEditable = 'true';
            el.classList.add('editable-field');
        });

        // Show save/cancel, hide edit button
        block.querySelector('.btn-edit')?.classList.add('d-none');
        block.querySelector('.block-controls')?.classList.remove('d-none');

        hasUnsaved = false;

        // Track changes
        block.querySelectorAll('[data-field]').forEach(function (el) {
            el.addEventListener('input', function () {
                hasUnsaved = true;
            });
        });
    }

    function deactivateBlock(block) {
        block.classList.remove('editing');
        block.querySelectorAll('[data-field]').forEach(function (el) {
            el.contentEditable = 'false';
            el.classList.remove('editable-field');
        });
        block.querySelector('.btn-edit')?.classList.remove('d-none');
        block.querySelector('.block-controls')?.classList.add('d-none');
        activeBlock = null;
        hasUnsaved = false;
        originalValues = {};
    }

    function cancelBlock(block) {
        // Restore original values
        block.querySelectorAll('[data-field]').forEach(function (el) {
            if (originalValues[el.dataset.field] !== undefined) {
                el.innerHTML = originalValues[el.dataset.field];
            }
        });
        deactivateBlock(block);
    }

    // ── Save ──────────────────────────────────────────────────

    function saveBlock(block) {
        const sectionId = block.dataset.sectionId;
        const entityType = block.dataset.entityType;
        const entityId = block.dataset.entityId;
        const saveUrl = block.dataset.saveUrl;

        // Collect field values
        const data = new FormData();
        block.querySelectorAll('[data-field]').forEach(function (el) {
            data.append(el.dataset.field, el.innerText.trim());
        });

        // Collect toggle values
        block.querySelectorAll('[data-toggle]').forEach(function (el) {
            data.append(el.dataset.toggle, el.dataset.value);
        });

        // Show saving state
        const saveBtn = block.querySelector('.btn-save');
        if (saveBtn) {
            saveBtn.textContent = 'Speichern...';
            saveBtn.disabled = true;
        }

        fetch(saveUrl, {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (res) { return res.json(); })
            .then(function (json) {
                if (json.success) {
                    showFeedback(block, 'Gespeichert ✓', 'success');
                    deactivateBlock(block);
                } else {
                    showFeedback(block, 'Fehler: '.json.error, 'error');
                    if (saveBtn) {
                        saveBtn.textContent = 'Speichern';
                        saveBtn.disabled = false;
                    }
                }
            })
            .catch(function () {
                showFeedback(block, 'Verbindungsfehler', 'error');
                if (saveBtn) {
                    saveBtn.textContent = 'Speichern';
                    saveBtn.disabled = false;
                }
            });
    }

    // ── Feedback message ──────────────────────────────────────

    function showFeedback(block, message, type) {
        let feedback = block.querySelector('.block-feedback');
        if (!feedback) {
            feedback = document.createElement('span');
            feedback.className = 'block-feedback';
            block.querySelector('.block-controls')?.appendChild(feedback);
        }
        feedback.textContent = message;
        feedback.className = 'block-feedback block-feedback--' + type;
        setTimeout(function () { feedback.textContent = ''; }, 3000);
    }

    // ── Toggle controls ───────────────────────────────────────

    function initToggles(block) {
        // Theme toggle
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

        // Layout toggle
        block.querySelectorAll('[data-action="toggle-layout"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const layouts = ['50-50', '75-25', '25-75'];
                const current = btn.closest('[data-toggle]').dataset.value || '50-50';
                const next = layouts[(layouts.indexOf(current) + 1) % layouts.length];
                btn.closest('[data-toggle]').dataset.value = next;
                btn.querySelector('.layout-label').textContent = next;
                hasUnsaved = true;
                // Full re-render needed for layout change — mark for save
            });
        });

        // Flip image position
        block.querySelectorAll('[data-action="toggle-flip"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const toggle = btn.closest('[data-toggle]');
                const current = toggle.dataset.value;
                const next = current === 'left' ? 'right' : 'left';
                toggle.dataset.value = next;
                hasUnsaved = true;
            });
        });

        // Object fit toggle
        block.querySelectorAll('[data-action="toggle-fit"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const toggle = btn.closest('[data-toggle]');
                const current = toggle.dataset.value || 'cover';
                const next = current === 'cover' ? 'contain' : 'cover';
                toggle.dataset.value = next;
                const img = block.querySelector('.section-image');
                if (img) img.style.objectFit = next;
                hasUnsaved = true;
            });
        });
    }

    // ── Event listeners ───────────────────────────────────────

    document.querySelectorAll('.editable-block').forEach(function (block) {
        // Edit button
        const editBtn = block.querySelector('.btn-edit');
        if (editBtn) {
            editBtn.addEventListener('click', function () {
                activateBlock(block);
                initToggles(block);
            });
        }

        // Save button
        const saveBtn = block.querySelector('.btn-save');
        if (saveBtn) {
            saveBtn.addEventListener('click', function () {
                saveBlock(block);
            });
        }

        // Cancel button
        const cancelBtn = block.querySelector('.btn-cancel');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () {
                if (hasUnsaved) {
                    if (!confirm('Änderungen verwerfen?')) return;
                }
                cancelBlock(block);
            });
        }
    });

    // ── Warn on page leave with unsaved changes ───────────────
    window.addEventListener('beforeunload', function (e) {
        if (hasUnsaved) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

});