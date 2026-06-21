<?php

/**
 * footer.php
 *
 * Site footer component.
 * Populate from organisation_info via OrganisationModel
 * (name, address, email, social links)
 */
?>

<footer class="text-center mt-auto segment-fix dark-segment">

    <div class="footer-logo margin-top">
        <img src="<?= htmlspecialchars($org->logo_url ?? '') ?>" alt="<?= htmlspecialchars($org->name ?? 'Organisation') ?> Logo">
    </div>

    <p class="">&copy; 2026 <?= htmlspecialchars($org->name ?? 'Organisation') ?></p>

    <nav class="nav-socials justify-content-center margin-top" aria-label="Socials Navigation">
        <?php foreach ($org->urls as $url): ?>
            <?php if ($url->type_label === 'Website') continue; ?>
            <a href="<?= htmlspecialchars($url->url) ?>"
                target="_blank"
                rel="noopener noreferrer"
                title="<?= htmlspecialchars($url->type_label) ?>">
                <i class="ti <?= htmlspecialchars($url->icon) ?>"></i>
            </a>
        <?php endforeach; ?>
    </nav>

    <nav class="footer-links small" aria-label="Footer Navigation">
        <a href="#">Datenschutzerklärung</a>
        <span class="divider">|</span>
        <a href="#">Impressum</a>
        <span class="divider">|</span>
        <a href="/kontakt">Kontakt</a>
    </nav>

</footer>