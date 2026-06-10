<?php

/**
 * hero.php
 *
 * Homepage hero section.
 * Fixed structure — always the first section on homepage.
 * Content driven by organisation_info — not a free section.
 *
 * $org available from BaseController::render()
 */
?>

<section class="segment dark-segment hero">
    <div class="container">
        <div class="row align-items-center g-5">

            <!-- Logo — left -->
            <div class="col-12 col-md-5 text-center">
                <?php if (!empty($org->logo_url)): ?>
                    <img src="<?= htmlspecialchars($org->logo_url) ?>"
                        alt="<?= htmlspecialchars($org->name ?? '') ?> Logo"
                        class="hero-logo">
                <?php else: ?>
                    <div class="hero-logo-placeholder">
                        <i class="ti ti-building-community"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Content — right -->
            <div class="col-12 col-md-7">
                <div class="section-content">

                    <?php if (!empty($org->name)): ?>
                        <h1><?= htmlspecialchars($org->name) ?></h1>
                    <?php endif; ?>

                    <?php if (!empty($org->tagline)): ?>
                        <h2><?= htmlspecialchars($org->tagline) ?></h2>
                    <?php endif; ?>

                    <?php if (!empty($org->description)): ?>
                        <p><?= htmlspecialchars($org->description) ?></p>
                    <?php endif; ?>

                    <a href="/veranstaltungen" class="btn-section">
                        Veranstaltungen
                    </a>

                </div>
            </div>

        </div>
    </div>
</section>