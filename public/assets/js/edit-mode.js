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
    // FREE SECTIONS — editable-block
    // ──────────────────────────────────────────────────────────

    function activateBlock(block) {
        if (activeBlock && activeBlock !== block && hasUnsaved) {
            if (!confirm('Ungespeicherte Änderungen verwerfen?')) return;
            cancelBlock(activeBlock);
        }
        if (activeBlock && activeBlock !== block) {
            deactivateBlock(activeBlock);
        }

        activeBlock = block;
        block.classList.add('editing');

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

        initToggles(block);
    }

    function deactivateBlock(block) {
        block.classList.remove('editing');
        block.querySelectorAll('[data-field]').forEach(function (el) {
            el.contentEditable = 'false';
            el.classList.remove('editable-field');
        });
        activeBlock = null;
        hasUnsaved = false;
        originalValues = {};
    }

    function cancelBlock(block) {
        block.querySelectorAll('[data-field]').forEach(function (el) {
            if (originalValues[el.dataset.field] !== undefined) {
                el.innerHTML = originalValues[el.dataset.field];
            }
        });
        deactivateBlock(block);
    }

    function saveBlock(block) {
        const saveUrl = block.dataset.saveUrl;
        const data = new FormData();

        block.querySelectorAll('[data-field]').forEach(function (el) {
            data.append(el.dataset.field, el.innerText.trim());
        });

        block.querySelectorAll('[data-toggle]').forEach(function (el) {
            data.append(el.dataset.toggle, el.dataset.value);
        });

        const saveBtn = block.querySelector('.btn-save');
        if (saveBtn) { saveBtn.textContent = 'Speichern...'; saveBtn.disabled = true; }

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
                    if (saveBtn) { saveBtn.textContent = 'Speichern'; saveBtn.disabled = false; }
                }
            })
            .catch(function () {
                showBlockFeedback(block, 'Verbindungsfehler', 'error');
                if (saveBtn) { saveBtn.textContent = 'Speichern'; saveBtn.disabled = false; }
            });
    }

    function showBlockFeedback(block, message, type) {
        let fb = block.querySelector('.block-feedback');
        if (!fb) {
            fb = document.createElement('span');
            fb.className = 'block-feedback';
            block.querySelector('.block-controls')?.appendChild(fb);
        }
        fb.textContent = message;
        fb.className = 'block-feedback block-feedback--' + type;
        setTimeout(function () { fb.textContent = ''; }, 3000);
    }

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
                hasUnsaved = true;
            });
        });

        // Flip
        block.querySelectorAll('[data-action="toggle-flip"]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const toggle = btn.closest('[data-toggle]');
                toggle.dataset.value = toggle.dataset.value === 'left' ? 'right' : 'left';
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
    }

    // Init section blocks
    document.querySelectorAll('.editable-block').forEach(function (block) {
        block.addEventListener('click', function (e) {
            if (e.target.closest('.btn-save') || e.target.closest('.btn-cancel')) return;
            if (!block.classList.contains('editing')) activateBlock(block);
        });

        block.querySelector('.btn-save')?.addEventListener('click', function () {
            saveBlock(block);
        });

        block.querySelector('.btn-cancel')?.addEventListener('click', function () {
            if (hasUnsaved && !confirm('Änderungen verwerfen?')) return;
            cancelBlock(block);
        });
    });

    // ──────────────────────────────────────────────────────────
    // ENTITY EDIT ROWS — inline pencil/save/cancel
    // ──────────────────────────────────────────────────────────

    document.querySelectorAll('.entity-edit-row').forEach(function (row) {
        const field = row.querySelector('.entity-field');
        const editBtn = row.querySelector('.entity-edit-btn');
        const saveBtn = row.querySelector('.entity-save-btn');
        const cancelBtn = row.querySelector('.entity-cancel-btn');
        const saveUrl = row.dataset.saveUrl;
        const fieldName = field?.dataset.field;
        let original = field?.innerText ?? '';

        editBtn?.addEventListener('click', function () {
            original = field.innerText;
            field.contentEditable = 'true';
            field.classList.add('editing');
            row.classList.add('editing');
            field.focus();
        });

        cancelBtn?.addEventListener('click', function () {
            field.innerText = original;
            field.contentEditable = 'false';
            field.classList.remove('editing');
            row.classList.remove('editing');
        });

        saveBtn?.addEventListener('click', function () {
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

    // ── Warn on page leave ────────────────────────────────────
    window.addEventListener('beforeunload', function (e) {
        if (hasUnsaved) { e.preventDefault(); e.returnValue = ''; }
    });

});