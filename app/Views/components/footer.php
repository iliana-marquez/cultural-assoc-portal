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
    <!-- <div class="footer-wave">
        <svg viewBox="0 0 710 100" preserveAspectRatio="none">
            <path
                d="M709.969 0H0C0 0 75 109.375 187.992 98.5269C300.984 87.6791 273 5 479 46C685 87 709.969 0 709.969 0Z"
                fill="#900000" />
        </svg>
    </div> -->
    <?php if (!empty($org->logo_url)): ?>
        <div class="footer-logo margin-top">
            <img src="<?= htmlspecialchars($org->logo_url) ?>" alt="<?= htmlspecialchars($org->name ?? 'Organisation') ?> Logo">
        </div>
    <?php endif; ?>

    <p class="">&copy; 2026 <?= htmlspecialchars($org->name ?? 'Organisation') ?></p>

    <!-- Newsletter-Strip -->
    <?php require __DIR__ . '/../components/newsletter-strip.php'; ?>

    <!-- Social Links -->
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
        <a href="/datenschutz" class="inline-link">Datenschutzerklärung</a>
        <span class="divider">|</span>
        <a href="/impressum" class="inline-link">Impressum</a>
        <span class="divider">|</span>
        <a href="/kontakt" class="inline-link">Kontakt</a>
    </nav>

</footer>