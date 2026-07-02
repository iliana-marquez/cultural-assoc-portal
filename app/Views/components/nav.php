<?php

/**
 * nav.php
 *
 * Main navigation component.
 * Desktop: Verein dropdown on hover, text swap on Alsergrund/Archiv.
 * Mobile: sidebar slides below navbar, hamburger toggles open/close.
 *
 * $org available from BaseController::render()
 */

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function navActive(string $path, string $current): string
{
    return $path === $current ? 'active' : '';
}

function navActivePrefix(string $prefix, string $current): string
{
    return str_starts_with($current, $prefix) ? 'active' : '';
}
?>

<header class="site-header light-segment">
    <div class="nav-container">

        <a href="/" class="nav-brand-link">
            <?php if (!empty($org->inline_logo_url)): ?>
                <img class="nav-brand" src="<?= htmlspecialchars($org->inline_logo_url) ?>"
                    alt="<?= htmlspecialchars($org->name ?? '') ?>">
            <?php else: ?>
                <span class="nav-brand-text"><?= htmlspecialchars($org->name ?? '') ?></span>
            <?php endif; ?>
        </a>

        <nav id="main-nav">
            <ul class="nav-links">

                <li class="has-dropdown">
                    <a href="/" class="inline-link dropdown-trigger <?= navActive('/', $currentPath) ?>">Verein</a>
                    <ul class="dropdown-menu">
                        <li><a href="/ueber-uns" class="inline-link <?= navActive('/ueber-uns', $currentPath) ?>">Über uns</a></li>
                        <li><a href="/team" class="inline-link <?= navActivePrefix('/team', $currentPath) ?>">Team</a></li>
                        <li><a href="/partner" class="inline-link <?= navActive('/partner', $currentPath) ?>">Partner</a></li>
                        <li><a href="/sponsoren" class="inline-link <?= navActive('/sponsoren', $currentPath) ?>">Sponsoren</a></li>
                        <li><a href="/kuenstlerinnen" class="inline-link <?= navActivePrefix('/kuenstlerinnen', $currentPath) ?>">Künstler:innen</a></li>
                        <li><a href="/mitglied-werden" class="inline-link <?= navActive('/mitglied-werden', $currentPath) ?>">Mitglied werden</a></li>
                    </ul>
                </li>

                <li><a href="/programm" class="inline-link <?= navActivePrefix('/programm', $currentPath) ?>">Programm</a></li>

                <li><a href="/alsergrund" class="inline-link <?= navActive('/alsergrund', $currentPath) ?>">Bezirksporträt</a></li>

                <li><a href="/archiv" class="inline-link <?= navActivePrefix('/archiv', $currentPath) ?>">Archiv</a></li>

                <li><a href="/kontakt" class="inline-link <?= navActive('/kontakt', $currentPath) ?>">Kontakt</a></li>

            </ul>
        </nav>

        <button class="hamburger" id="hamburger" aria-label="Menü öffnen" aria-expanded="false">
            <i class="ti ti-menu-2" id="hamburger-icon"></i>
        </button>

    </div>
</header>

<aside class="sidebar light-segment" id="sidebar">
    <ul class="sidebar-links">

        <li>
            <a href="/" class="inline-link sidebar-section-label <?= navActive('/', $currentPath) ?>">
                Verein
            </a>
            <ul class="sidebar-submenu">
                <li><a href="/ueber-uns" class="inline-link <?= navActive('/ueber-uns', $currentPath) ?>">Über uns</a></li>
                <li><a href="/team" class="inline-link <?= navActivePrefix('/team', $currentPath) ?>">Team</a></li>
                <li><a href="/partner" class="inline-link <?= navActive('/partner', $currentPath) ?>">Partner</a></li>
                <li><a href="/sponsoren" class="inline-link <?= navActive('/sponsoren', $currentPath) ?>">Sponsoren</a></li>
                <li><a href="/kuenstlerinnen" class="inline-link <?= navActivePrefix('/kuenstlerinnen', $currentPath) ?>">Künstler:innen</a></li>
                <li><a href="/mitglied-werden" class="inline-link <?= navActive('/mitglied-werden', $currentPath) ?>">Mitglied werden</a></li>
            </ul>
        </li>

        <li><a href="/programm" class="inline-link <?= navActivePrefix('/programm', $currentPath) ?>">Programm</a></li>
        <li><a href="/kontakt" class="inline-link <?= navActive('/kontakt', $currentPath) ?>">Kontakt</a></li>
        <li><a href="/alsergrund" class="inline-link <?= navActive('/alsergrund', $currentPath) ?>">Bezirksporträt</a></li>
        <li><a href="/archiv" class="inline-link <?= navActivePrefix('/archiv', $currentPath) ?>">Archiv</a></li>

        <li class="nav-socials">
            <?php foreach ($org->urls as $url): ?>
                <?php if ($url->type_label === 'Website') continue; ?>
                <a href="<?= htmlspecialchars($url->url) ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    title="<?= htmlspecialchars($url->type_label) ?>">
                    <i class="ti <?= htmlspecialchars($url->icon) ?>"></i>
                </a>
            <?php endforeach; ?>
        </li>

    </ul>
</aside>