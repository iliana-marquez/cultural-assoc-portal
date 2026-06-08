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

<header class="site-header">
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
                <li><a href="/kontakt">Kontakt</a></li>

                <li>
                    <a href="/alsergrund" class="swap-label">
                        <span class="label-main">Alsergrund</span>
                        <span class="label-hover">Bezirksporträt</span>
                    </a>
                </li>

                <li>
                    <a href="/archiv" class="swap-label">
                        <span class="label-main">Archiv</span>
                        <span class="label-hover">Vermächtnis</span>
                    </a>
                </li>

            </ul>
        </nav>

        <button class="hamburger" id="hamburger" aria-label="Menü öffnen" aria-expanded="false">
            <i class="ti ti-menu-2"></i>
        </button>

    </div>
</header>

<aside class="sidebar" id="sidebar">
    <ul class="sidebar-links">

        <li>
            <a href="/" class="sidebar-section-label">
                Verein
                <i class="ti ti-chevron-down" aria-hidden="true"></i>
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

    </ul>
</aside>