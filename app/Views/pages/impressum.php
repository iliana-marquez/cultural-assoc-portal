<?php

/**
 * impressum.php
 *
 * Impressum — legally required page.
 * Org data pulled from organisation_info (dynamic).
 * President pulled from team table (role LIKE '%präsident%').
 * Free sections managed via edit mode.
 *
 * Variables:
 *   $org        object  Organisation data
 *   $president  object|null  Team member with president role
 *   $sections   array   Free sections from PagesModel
 *   $isLoggedIn bool
 */

$legalRepName = $legalRep ? TeamModel::displayName($legalRep) : null;
?>

<section class="segment dark-segment">
    <div class="container">
        <h1>Impressum</h1>
        <hr>
        <address>
            <strong><?= htmlspecialchars($org->name) ?></strong><br>

            <?php if (!empty($org->street)): ?>
                <p class="contact-address">
                    <i class="ti ti-map-pin"></i>
                    <?= htmlspecialchars($org->street) ?>,
                    <?= htmlspecialchars($org->postcode) ?>
                    <?= htmlspecialchars($org->city) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($org->email)): ?>
                <a href="mailto:<?= htmlspecialchars($org->email) ?>" class="contact-link">
                    <i class="ti ti-mail"></i>
                    <?= htmlspecialchars($org->email) ?>
                </a><br>
            <?php endif; ?>

            <?php if (!empty($org->phone)): ?>
                <a href="tel:<?= htmlspecialchars($org->phone) ?>" class="contact-link">
                    <i class="ti ti-phone"></i>
                    <?= htmlspecialchars($org->phone) ?>
                </a><br>
            <?php endif; ?>

            <?php if (!empty($org->registration_number)): ?>
                <p>
                    ZVR: <?= htmlspecialchars($org->registration_number) ?>
                </p>
            <?php endif; ?>
        </address>


        <?php if ($legalRepName): ?>
            <?php if (!empty($legalRep->role)): ?>
                <?= htmlspecialchars($legalRep->role) ?>:
            <?php endif; ?>
            <?= htmlspecialchars($legalRepName) ?><br>
        <?php else: ?>
            <em>Information folgt in Kürze.</em><br>
        <?php endif; ?>
    </div>
</section>

<?php $sectionsMode = 'all'; ?>
<?php require __DIR__ . '/../components/sections/render-sections.php'; ?>