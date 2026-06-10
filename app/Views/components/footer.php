<?php

/**
 * footer.php
 *
 * Site footer component.
 * Populate from organisation_info via OrganisationModel
 * (name, address, email, social links)
 */
?>

<footer class="text-center mt-auto segment dark-segment">

    <div class="footer-logo margin-top">
        <img src="<?= htmlspecialchars($org->logo_url ?? '') ?>" alt="<?= htmlspecialchars($org->name ?? 'Organisation') ?> Logo">
    </div>

    <p class="">&copy; 2026 <?= htmlspecialchars($org->name ?? 'Organisation') ?></p>

    <nav class="nav-socials justify-content-center margin-top" aria-label="Socials Navigation">
        <a href=""><i class="ti ti-brand-instagram"></i></a>
        <a href=""><i class="ti ti-brand-facebook"></i></a>
        <a href=""><i class="ti ti-brand-youtube"></i></a>
    </nav>

    <nav class="footer-links small" aria-label="Footer Navigation">
        <a href="#">Datenschutzerklärung</a>
        <span class="divider">|</span>
        <a href="#">Impressum</a>
        <span class="divider">|</span>
        <a href="/impressum">Kontakt</a>
    </nav>

</footer>