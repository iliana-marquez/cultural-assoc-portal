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

                <li>
                    <a href="/alsergrund" class="swap-label">
                        <span class="label-main">Alsergrund</span>
                        <span class="label-hover small">Bezirksporträt</span>
                    </a>
                </li>

                <li>
                    <a href="/archiv" class="swap-label">
                        <span class="label-main">Archiv</span>
                        <span class="label-hover small">Vermächtnis</span>
                    </a>
                </li>

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
        <li><a href="/alsergrund">Alsergrund</a></li>
        <li><a href="/archiv">Archiv</a></li>

        <li class="nav-socials">
            <a href=""><i class="ti ti-brand-instagram"></i></a>
            <a href=""><i class="ti ti-brand-facebook"></i></a>
            <a href=""><i class="ti ti-brand-youtube"></i></a>
        </li>

    </ul>
</aside>