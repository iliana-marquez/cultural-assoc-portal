<?php

/**
 * components/newsletter-strip.php
 *
 * Reusable newsletter signup strip — included in footer.php.
 * Email only — minimal friction.
 * CSRF token generated on every page load via BaseController.
 * JS handler in app.js — data-action="newsletter-subscribe".
 */
?>
<div class="newsletter-strip">
    <p class="newsletter-strip__label">
        Bleiben Sie informiert:
    </p>

    <div class="newsletter-strip-form">

        <form class="newsletter-strip__form" data-action="newsletter-subscribe">
            <input
                type="hidden"
                id="csrf-newsletter"
                name="csrf_newsletter"
                value="<?= htmlspecialchars($_SESSION['csrf_newsletter'] ?? '') ?>">

            <input
                class="newsletter-strip__input"
                type="email"
                id="newsletter-email"
                placeholder="Ihre E-Mail-Adresse"
                maxlength="200"
                autocomplete="email">

            <button
                class="newsletter-strip__button"
                type="button"
                id="newsletter-submit">
                Anmelden
            </button>
        </form>
    </div>

    <p class="newsletter-strip__feedback" id="newsletter-feedback"></p>

    <small class="newsletter-strip__terms">
        Mit der Anmeldung stimmen Sie der Verarbeitung Ihrer E-Mail-Adresse gemäß unserer
        <a href="/datenschutz" class="inline-link disclaimer">Datenschutzerklärung</a> zu.
    </small>
</div>