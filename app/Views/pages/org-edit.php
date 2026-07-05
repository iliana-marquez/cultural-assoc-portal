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

            <!-- Logos -->
            <div class="col-12">
                <h3>Logos</h3>
                <div class="row g-4">

                    <!-- Logo — Hero / Footer -->
                    <div class="col-12 col-md-6">
                        <div class="media-edit-row" data-entity-type="organisation" data-entity-id="<?= $org->id ?>" data-stage="logo">
                            <div class="edit-row-header">
                                <label class="edit-row-label">Logo (Home-Hero / Footer)</label>
                                <div class="edit-row-actions">
                                    <span class="entity-feedback"></span>
                                    <button class="entity-edit-btn org-logo-pencil-btn"><i class="ti ti-pencil"></i></button>
                                    <button class="entity-cancel-btn org-logo-cancel-btn" style="display:none;"><i class="ti ti-x"></i></button>
                                </div>
                            </div>
                            <div class="org-logo-item p-2" data-field="logo_url">
                                <?php if (!empty($org->logo_url)): ?>
                                    <label class="entity-edit-btn border-0" style="cursor:pointer; display:none;" title="Logo ersetzen">
                                        <i class="ti ti-pencil"></i>
                                        <input type="file" accept="image/*" class="d-none"
                                            data-action="upload-org-logo"
                                            data-field="logo_url">
                                    </label>
                                    <button class="entity-remove-btn border-0" style="display:none;" data-action="delete-org-logo" data-field="logo_url">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                    <img src="<?= htmlspecialchars($org->logo_url) ?>" alt="Logo" style="max-height:80px;">
                                <?php else: ?>
                                    <p class="text-muted mb-0">— kein Logo —</p>
                                <?php endif; ?>
                            </div>
                            <?php if (empty($org->logo_url)): ?>
                                <div class="org-logo-upload-wrap p-2" style="display:none;">
                                    <label class="entity-edit-btn" style="cursor:pointer;">
                                        <i class="ti ti-photo-plus"></i> Logo hochladen
                                        <input type="file" accept="image/*" class="d-none"
                                            data-action="upload-org-logo"
                                            data-field="logo_url">
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Inline logo — Navbar -->
                    <div class="col-12 col-md-6">
                        <div class="media-edit-row" data-entity-type="organisation" data-entity-id="<?= $org->id ?>" data-stage="inline-logo">
                            <div class="edit-row-header">
                                <label class="edit-row-label">Inline-Logo (Navbar)</label>
                                <div class="edit-row-actions">
                                    <span class="entity-feedback"></span>
                                    <button class="entity-edit-btn org-logo-pencil-btn"><i class="ti ti-pencil"></i></button>
                                    <button class="entity-cancel-btn org-logo-cancel-btn" style="display:none;"><i class="ti ti-x"></i></button>
                                </div>
                            </div>
                            <div class="org-logo-item p-2" data-field="inline_logo_url">
                                <?php if (!empty($org->inline_logo_url)): ?>
                                    <label class="entity-edit-btn border-0" style="cursor:pointer; display:none;" title="Inline-Logo ersetzen">
                                        <i class="ti ti-pencil"></i>
                                        <input type="file" accept="image/*" class="d-none"
                                            data-action="upload-org-logo"
                                            data-field="inline_logo_url">
                                    </label>
                                    <button class="entity-remove-btn border-0" style="display:none;" data-action="delete-org-logo" data-field="inline_logo_url">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                    <img src="<?= htmlspecialchars($org->inline_logo_url) ?>" alt="Inline Logo" style="max-height:50px;">
                                <?php else: ?>
                                    <p class="text-muted mb-0">— kein Inline-Logo —</p>
                                <?php endif; ?>
                            </div>
                            <?php if (empty($org->inline_logo_url)): ?>
                                <div class="org-logo-upload-wrap p-2" style="display:none;">
                                    <label class="entity-edit-btn" style="cursor:pointer;">
                                        <i class="ti ti-photo-plus"></i> Inline-Logo hochladen
                                        <input type="file" accept="image/*" class="d-none"
                                            data-action="upload-org-logo"
                                            data-field="inline_logo_url">
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Legal representative — controls team.order_index 0,
                 not an organisation_info field. Lives here because the
                 editor manages this from Vereinsinfo in one place, not
                 by hunting through individual team profiles. -->
            <div class="col-12 col-md-6">
                <h3>Gesetzliche Vertretung</h3>
                <div class="entity-select-row" data-save-url="/<?= htmlspecialchars($config['admin_path']) ?>/org/legal-representative">
                    <div class="edit-row-header">
                        <label class="edit-row-label">Gesetzliche:r Vertreter:in (Datenschutz / Impressum)</label>
                        <div class="edit-row-actions">
                            <span class="entity-feedback"></span>
                            <button class="entity-edit-btn"><i class="ti ti-pencil"></i></button>
                            <button class="entity-save-btn" style="display:none;"><i class="ti ti-check"></i></button>
                            <button class="entity-cancel-btn" style="display:none;"><i class="ti ti-x"></i></button>
                        </div>
                    </div>
                    <p class="entity-select-display m-2">
                        <?= $legalRep ? htmlspecialchars(TeamModel::displayName($legalRep) . (!empty($legalRep->role) ? ' — ' . $legalRep->role : '')) : '— niemand festgelegt —' ?>
                    </p>
                    <select class="entity-field entity-select" data-field="team_id">
                        <option value="">— bitte wählen —</option>
                        <?php foreach ($teamMembers as $member): ?>
                            <option value="<?= $member->id ?>" <?= ($legalRep && $legalRep->id === $member->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(TeamModel::displayName($member)) ?><?= !empty($member->role) ? ' — ' . htmlspecialchars($member->role) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Bankverbindung -->
            <div class="col-12 col-md-6">
                <h3>Bankverbindung</h3>
                <?= editRow('Kontoinhaber',  'account_holder',   $org->account_holder  ?? '', $saveUrl) ?>
                <?= editRow('IBAN',          'iban',             $org->iban            ?? '', $saveUrl) ?>
                <?= editRow('BIC',           'bic',              $org->bic             ?? '', $saveUrl) ?>
            </div>

            <!-- Membership -->
            <div class="col-12 col-md-6">
                <h3>Mitgliedschaft</h3>
                <?= editRow('Überweisung Verwendungszweck Mitgliedschaft', 'payment_purpose',  $org->payment_purpose  ?? '', $saveUrl) ?>
                <?= editRow('Mitgliedsbeitrag (€)', 'membership_fee', $org->membership_fee ?? '', $saveUrl) ?>
                <?= editRow('Mitgliedschaftshinweis', 'membership_note', $org->membership_note ?? '', $saveUrl) ?>
            </div>

            <!-- Spenden -->
            <div class="col-12 col-md-6">
                <h3>Spenden</h3>
                <?= editRow('Überweisung Verwendungszweck Spende',         'donation_purpose', $org->donation_purpose ?? '', $saveUrl) ?>
                <?= editRow('Spendenhinweis (steuerliche Absetzbarkeit etc.)', 'donation_note', $org->donation_note ?? '', $saveUrl) ?>
            </div>



        </div>
    </div>
</section>