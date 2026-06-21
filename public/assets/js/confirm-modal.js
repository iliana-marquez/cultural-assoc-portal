// ── confirm_modal ───────────────────────────────────────────
// Single, generic owner of #confirmModal. Replaces native confirm()
// for destructive/consequential actions, letting the confirm button
// name the real consequence instead of a generic "OK".
//
// Usage:
//   openConfirmModal({
//       title: 'Eintrag entfernen',
//       message: 'Dieser Eintrag ist nirgendwo sonst verknüpft. ' +
//                 'Beim Entfernen wird er endgültig gelöscht.',
//       confirmLabel: 'Endgültig entfernen',
//       onConfirm: function () { ... caller's actual action ... }
//   });
//
// onConfirm is called once, then the modal closes automatically.
// The caller does not need to call closeConfirmModal() itself in
// the success case — only Abbrechen and the backdrop click do that
// directly. (Unlike input_modal, there's no "keep open on error"
// need here, since confirm_modal doesn't collect any input that
// could be lost.)

const confirmModal = {
    el: document.getElementById('confirmModal'),
    bound: false,
    onConfirm: null
};

function bindConfirmModalOnce() {
    if (confirmModal.bound || !confirmModal.el) return;
    confirmModal.bound = true;

    const cancelBtn = confirmModal.el.querySelector('.confirm-modal-cancel');
    const confirmBtn = confirmModal.el.querySelector('.confirm-modal-confirm');
    const closeBtn = confirmModal.el.querySelector('.confirm-modal-close');

    cancelBtn?.addEventListener('click', closeConfirmModal);
    closeBtn?.addEventListener('click', closeConfirmModal);

    confirmModal.el.addEventListener('click', function (e) {
        if (e.target === confirmModal.el) closeConfirmModal();
    });

    confirmBtn?.addEventListener('click', function () {
        const callback = confirmModal.onConfirm;
        closeConfirmModal();
        if (typeof callback === 'function') callback();
    });
}

function openConfirmModal(config) {
    bindConfirmModalOnce();
    if (!confirmModal.el) return;

    confirmModal.onConfirm = config.onConfirm || null;

    const titleEl = confirmModal.el.querySelector('.confirm-modal-title');
    const messageEl = confirmModal.el.querySelector('.confirm-modal-message');
    const confirmBtn = confirmModal.el.querySelector('.confirm-modal-confirm');

    if (titleEl) titleEl.textContent = config.title || '';
    if (messageEl) messageEl.textContent = config.message || '';
    if (confirmBtn) confirmBtn.textContent = config.confirmLabel || 'Bestätigen';

    confirmModal.el.style.display = 'flex';
}

function closeConfirmModal() {
    if (confirmModal.el) confirmModal.el.style.display = 'none';
    confirmModal.onConfirm = null;
}