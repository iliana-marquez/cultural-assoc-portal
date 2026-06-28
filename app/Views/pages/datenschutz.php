<?php

/**
 * datenschutz.php
 *
 * Datenschutzerklärung — legally required page.
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

$presidentName = $president
    ? TeamModel::displayName($president)
    : null;
?>

<section class="segment dark-segment">
    <div class="container">
        <h1>Datenschutzerklärung</h1>
        <hr>
        <h3>Verantwortlicher für die Datenverarbeitung ist:</h3>
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
                </a>
            <?php endif; ?>
        </address>

        <?php if ($presidentName): ?>
            <?= htmlspecialchars($president->role) ?>:
            <?= htmlspecialchars($presidentName) ?><br>
        <?php else: ?>
            <em>Information folgt in Kürze.</em><br>
        <?php endif; ?>
    </div>
</section>

<?php $sectionsMode = 'all'; ?>
<?php require __DIR__ . '/../components/sections/render-sections.php'; ?>