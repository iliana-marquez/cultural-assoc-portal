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
?>

<header class="site-header light-segment">
    <div class="nav-container">

        <a href="/" class="nav-brand">
            <?= htmlspecialchars($org->name ?? 'Organisation') ?>
        </a>

        <nav id="main-nav">
            <ul class="nav-links">

                <li class="has-dropdown">
                    <a href="/" class="dropdown-trigger">Verein</a>
                    <ul class="dropdown-menu">
                        <li><a href="/ueber-uns">Über uns</a></li>
                        <li><a href="/team">Team</a></li>
                        <li><a href="/partner">Partner</a></li>
                        <li><a href="/sponsoren">Sponsoren</a></li>
                        <li><a href="/kuenstlerinnen">Künstler:innen</a></li>
                        <li><a href="/mitglied-werden">Mitglied werden</a></li>
                    </ul>
                </li>

                <li><a href="/veranstaltungen">Veranstaltungen</a></li>

                <li><a href="/alsergrund">Bezirksporträt</a></li>

                <li><a href="/archiv">Archiv</a></li>

                <li><a href="/kontakt">Kontakt</a></li>

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
            <a href="/" class="sidebar-section-label">
                Verein
            </a>
            <ul class="sidebar-submenu">
                <li><a href="/ueber-uns">Über uns</a></li>
                <li><a href="/team">Team</a></li>
                <li><a href="/partner">Partner</a></li>
                <li><a href="/sponsoren">Sponsoren</a></li>
                <li><a href="/kuenstlerinnen">Künstler:innen</a></li>
                <li><a href="/mitglied-werden">Mitglied werden</a></li>
            </ul>
        </li>

        <li><a href="/veranstaltungen">Veranstaltungen</a></li>
        <li><a href="/kontakt">Kontakt</a></li>
        <li><a href="/alsergrund">Bezirksporträt</a></li>
        <li><a href="/archiv">Archiv</a></li>

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