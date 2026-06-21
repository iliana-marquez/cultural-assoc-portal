<?php

/**
 * org-edit.php
 * Organisation info edit page — editors only.
 */
$saveUrl = '/' . $config['admin_path'] . '/org/save';

function editRow(string $label, string $field, string $value, string $saveUrl): string
{
    return '
    <div class="entity-edit-row" data-save-url="' . htmlspecialchars($saveUrl) . '">
        <div class="edit-row-header">
            <label class="edit-row-label">' . htmlspecialchars($label) . '</label>
            <div class="edit-row-actions">
                <span class="entity-feedback"></span>
                <button class="entity-edit-btn"><i class="ti ti-pencil"></i></button>
                <button class="entity-save-btn"><i class="ti ti-check"></i></button>
                <button class="entity-cancel-btn"><i class="ti ti-x"></i></button>
            </div>
        </div>
        <span class="entity-field" data-field="' . htmlspecialchars($field) . '">' . htmlspecialchars($value) . '</span>
    </div>';
}
?>

<section class="segment light-segment">
    <div class="container">

        <h1><?= htmlspecialchars($org->name) ?></h1>
        <p class="opacity-50 mb-4">Vereinsdatenverwaltung (nur für Redakteur:innen)</p>

        <div class="row g-5">

            <!-- Identity -->
            <div class="col-12 col-md-6">
                <h3>Identität</h3>
                <?= editRow('Name',              'name',                $org->name              ?? '', $saveUrl) ?>
                <?= editRow('Tagline',           'tagline',             $org->tagline           ?? '', $saveUrl) ?>
                <?= editRow('Organisationstyp',  'organisation_type',   $org->organisation_type ?? '', $saveUrl) ?>
                <?= editRow('ZVR / Registernummer', 'registration_number', $org->registration_number ?? '', $saveUrl) ?>
                <?= editRow('Statuten URL',      'statutes_url',        $org->statutes_url      ?? '', $saveUrl) ?>
                <!-- URLs -->
                <?php
                $entityType = 'organisation';
                $entityId   = $org->id;
                include __DIR__ . '/../components/entity-urls.php';
                ?>
            </div>

            <!-- Contact -->
            <div class="col-12 col-md-6">
                <h3>Kontakt</h3>
                <?= editRow('E-Mail',   'email',    $org->email    ?? '', $saveUrl) ?>
                <?= editRow('Telefon',  'phone',    $org->phone    ?? '', $saveUrl) ?>
                <?= editRow('Straße',   'street',   $org->street   ?? '', $saveUrl) ?>
                <?= editRow('PLZ',      'postcode', $org->postcode ?? '', $saveUrl) ?>
                <?= editRow('Stadt',    'city',     $org->city     ?? '', $saveUrl) ?>
                <?= editRow('Land',     'country',  $org->country  ?? '', $saveUrl) ?>
            </div>

            <!-- Descriptions -->
            <div class="col-12">
                <h3>Beschreibungen</h3>
                <?= editRow('Kurzbeschreibung (SEO · max. 160 Zeichen)', 'seo_description', $org->seo_description ?? '', $saveUrl) ?>
                <?= editRow('Langbeschreibung (Homepage)',                'description',     $org->description    ?? '', $saveUrl) ?>
            </div>



        </div>
    </div>
</section>