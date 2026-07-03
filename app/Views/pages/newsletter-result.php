<?php

/**
 * newsletter-result.php
 *
 * Generic result page for confirm and unsubscribe actions.
 * Variables:
 *   $success bool
 *   $message string
 */
?>
<section class="segment light-segment">
    <div class="container text-center">
        <?php if ($success): ?>
            <i class="ti ti-heart-handshake" style="font-size:5rem;"></i>
        <?php else: ?>
            <i class="ti ti-circle-x" style="font-size:3rem; color: var(--feedback-error);"></i>
        <?php endif; ?>
        <p class="mt-3"><?= htmlspecialchars($message) ?></p>
        <a href="/" class="nav-icon-ux mt-3 d-inline-flex">
            <i class="ti ti-arrow-left"></i> Zur Startseite
        </a>
    </div>
</section>