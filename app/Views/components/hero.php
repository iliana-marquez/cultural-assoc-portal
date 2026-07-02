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
$hasLogo = !empty($org->logo_url);
?>

<section class="segment dark-segment hero">
    <div class="container">
        <div class="row align-items-center g-5">

            <?php if ($hasLogo): ?>
                <!-- Logo — left -->
                <div class="col-12 col-md-5 text-center">
                    <img src="<?= htmlspecialchars($org->logo_url) ?>"
                        alt="<?= htmlspecialchars($org->name ?? '') ?> Logo"
                        class="hero-logo">
                </div>
            <?php endif; ?>

            <!-- Content -->
            <div class="<?= $hasLogo ? 'col-12 col-md-7' : 'col-12' ?>">
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

                    <div class="hero-actions">
                        <a href="/veranstaltungen" class="btn-section">
                            <i class="ti ti-calendar-check"></i>
                            Was kommt?
                        </a>

                        <a href="/archiv" class="btn-section btn-section-outline">
                            <i class="ti ti-history"></i>
                            Rückblick
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>