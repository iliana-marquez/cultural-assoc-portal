<?php

/**
 * components/modals/feedback-modal.php
 *
 * Reusable feedback modal for positive confirmations requiring no action.
 * Closes on X click or any click outside the card.
 * Controlled via openFeedbackModal() in feedback-modal.js.
 *
 * Used for: newsletter subscribe success, contact form success,
 * event registration confirmation, newsletter send confirmation.
 */
?>
<div class="public-modal" id="feedbackModal" style="display:none;">
    <div class="public-modal-card light-segment">
        <div class="public-modal-header">
            <h4 class="public-modal-title"></h4>
            <button class="public-modal-close" aria-label="Schließen">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <p class="public-modal-message"></p>
    </div>
</div>