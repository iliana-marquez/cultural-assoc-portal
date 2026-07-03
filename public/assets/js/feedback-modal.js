// ── feedback_modal ──────────────────────────────────────────
// Single, generic owner of #feedbackModal. Used for positive
// confirmations that require no action from the user —
// newsletter subscribe success, contact form success, event
// registration confirmation, newsletter send confirmation.
//
// Closes on X click or any click outside the card.
// No action buttons — no decision required.
//
// Usage:
//   openFeedbackModal({
//       title: 'Vielen Dank!',
//       message: 'Ihre Nachricht wurde gesendet.'
//   });

const feedbackModal = {
    el: document.getElementById('feedbackModal'),
    bound: false
};

function bindFeedbackModalOnce() {
    if (feedbackModal.bound || !feedbackModal.el) return;
    feedbackModal.bound = true;

    feedbackModal.el.querySelector('.public-modal-close')
        ?.addEventListener('click', closeFeedbackModal);

    feedbackModal.el.addEventListener('click', function (e) {
        if (e.target === feedbackModal.el) closeFeedbackModal();
    });
}

function openFeedbackModal(config) {
    bindFeedbackModalOnce();
    if (!feedbackModal.el) return;

    const titleEl = feedbackModal.el.querySelector('.public-modal-title');
    const messageEl = feedbackModal.el.querySelector('.public-modal-message');

    if (titleEl) titleEl.textContent = config.title || '';
    if (messageEl) messageEl.textContent = config.message || '';

    feedbackModal.el.style.display = 'flex';
}

function closeFeedbackModal() {
    if (feedbackModal.el) feedbackModal.el.style.display = 'none';
}