<?php

/**
 * team-detail.php
 *
 * Team member detail page with inline editing for logged-in editors.
 * Edit rows follow the entity-edit-row pattern from edit-mode.js.
 * Profile image via entity_media (stage='profile') — no direct image column.
 *
 * Variables:
 *   $member     object  Team member with profileImg, urls
 *   $isLoggedIn bool    From BaseController
 */

$saveUrl = '/team/' . $member->id . '/save';

$editRow = function (string $label, string $field, string $value, string $saveUrl): string {
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
};

// Role suggestions — both gender forms listed as separate options for
// now (gender-aware role generation pinned for v2). "Sonstiges" reveals
// a free-text input — see role-select handling in edit-mode.js.
$roleOptions = [
    'Präsident',
    'Präsidentin',
    'Vizepräsident',
    'Vizepräsidentin',
    'Schriftführer',
    'Schriftführerin',
    'Schriftführer-Stv.',
    'Schriftführerin-Stv.',
    'Finanzreferent',
    'Finanzreferentin',
];
$isCustomRole = !empty($member->role) && !in_array($member->role, $roleOptions, true);
?>

<section class="segment light-segment">
    <div class="container">

        <?php if ($isLoggedIn): ?>
            <?php
            $isDraft     = ($member->status ?? 'draft') === 'draft';
            $isPublished = ($member->status ?? '') === 'published';
            ?>
            <div class="event-status-bar">
                <?php if ($isDraft): ?>
                    <span class="event-status-chip event-status-chip--draft">
                        Dieses Teammitglied ist ein Entwurf:
                    </span>
                    <button class="btn-section btn-section--primary"
                        data-action="publish-team"
                        data-team-id="<?= $member->id ?>">
                        Veröffentlichen
                    </button>
                    <button class="btn-section btn-section--danger"
                        data-action="delete-team"
                        data-team-id="<?= $member->id ?>">
                        <i class="ti ti-trash"></i> Löschen
                    </button>
                <?php elseif ($isPublished): ?>
                    <span class="event-status-chip event-status-chip--published">
                        Dieses Teammitglied ist veröffentlicht:
                    </span>
                    <button class="btn-section"
                        data-action="unpublish-team"
                        data-team-id="<?= $member->id ?>">
                        Als Entwurf setzen
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>


        <div class="row align-items-start g-5">

            <!-- Profile image -->
            <div class="col-12 col-md-4">
                <?php if (!empty($member->profileImg) || $isLoggedIn): ?>
                    <div class="media-edit-row"
                        data-entity-type="team"
                        data-entity-id="<?= $member->id ?>"
                        data-entity-slug="<?= htmlspecialchars($member->slug) ?>"
                        data-stage="profile"
                        data-fragment-url="/team/<?= $member->id ?>/profile-fragment">

                        <?php if ($isLoggedIn): ?>
                            <div class="edit-row-header">
                                <label class="edit-row-label">Foto</label>
                                <div class="edit-row-actions">
                                    <span class="entity-feedback"></span>
                                    <label class="entity-edit-btn media-upload-btn" style="cursor:pointer;" title="Foto hochladen">
                                        <i class="ti ti-photo-plus"></i>
                                        <input type="file" accept="image/*" class="d-none"
                                            data-action="upload-entity-image"
                                            data-entity-type="team"
                                            data-entity-id="<?= $member->id ?>"
                                            data-entity-slug="<?= htmlspecialchars($member->slug) ?>"
                                            data-stage="profile">
                                    </label>
                                    <button class="entity-edit-btn media-pencil-btn"><i class="ti ti-pencil"></i></button>
                                    <button class="entity-cancel-btn media-cancel-btn"><i class="ti ti-x"></i></button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="media-promo-content">
                            <?php
                            $entity     = $member;
                            $entityType = 'team';
                            $profileImg = $member->profileImg ?? null;
                            include __DIR__ . '/../components/profile-img.php';
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Content -->
            <div class="col-12 col-md-8">
                <div class="section-content">

                    <?php if ($isLoggedIn): ?>

                        <?= $editRow('Titel (Mag., Dr., …)', 'title',       $member->title      ?? '', $saveUrl) ?>
                        <?= $editRow('Vorname',              'first_name',  $member->first_name ?? '', $saveUrl) ?>
                        <?= $editRow('Nachname',             'last_name',   $member->last_name  ?? '', $saveUrl) ?>
                        <div class="entity-select-row" data-save-url="<?= htmlspecialchars($saveUrl) ?>">
                            <div class="edit-row-header">
                                <label class="edit-row-label">Rolle / Funktion</label>
                                <div class="edit-row-actions">
                                    <span class="entity-feedback"></span>
                                    <button class="entity-edit-btn"><i class="ti ti-pencil"></i></button>
                                    <button class="entity-save-btn" style="display:none;"><i class="ti ti-check"></i></button>
                                    <button class="entity-cancel-btn" style="display:none;"><i class="ti ti-x"></i></button>
                                </div>
                            </div>
                            <p class="entity-select-display m-2">
                                <?= !empty($member->role) ? htmlspecialchars($member->role) : '—' ?>
                            </p>
                            <select class="entity-field entity-select role-select" data-field="role">
                                <option value="">— keine Angabe —</option>
                                <?php foreach ($roleOptions as $option): ?>
                                    <option value="<?= htmlspecialchars($option) ?>" <?= $member->role === $option ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($option) ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="__custom__" <?= $isCustomRole ? 'selected' : '' ?>>Sonstiges (freier Text)</option>
                            </select>
                            <input type="text" class="role-custom-input"
                                placeholder="Eigene Rolle eingeben..."
                                value="<?= $isCustomRole ? htmlspecialchars($member->role) : '' ?>"
                                style="<?= $isCustomRole ? '' : 'display:none;' ?>">
                        </div>
                        <?= $editRow('Beruf / Profession',   'profession',  $member->profession ?? '', $saveUrl) ?>
                        <?= $editRow('Motto',                'motto',       $member->motto      ?? '', $saveUrl) ?>
                        <?= $editRow('Biografie',            'biography',   $member->biography  ?? '', $saveUrl) ?>

                    <?php else: ?>

                        <h1><?= htmlspecialchars($member->displayName) ?></h1>

                        <?php if (!empty($member->role)): ?>
                            <h2><?= htmlspecialchars($member->role) ?></h2>
                        <?php endif; ?>

                        <?php if (!empty($member->profession)): ?>
                            <p><strong><?= htmlspecialchars($member->profession) ?></strong></p>
                        <?php endif; ?>

                        <?php if (!empty($member->motto)): ?>
                            <blockquote><?= htmlspecialchars($member->motto) ?></blockquote>
                        <?php endif; ?>

                        <?php if (!empty($member->biography)): ?>
                            <p><?= nl2br(htmlspecialchars($member->biography)) ?></p>
                        <?php endif; ?>

                    <?php endif; ?>

                    <!-- Links -->
                    <?php if (!empty($member->urls) || $isLoggedIn): ?>
                        <?php
                        $entityType = 'team';
                        $entityId   = $member->id;
                        $urls       = $member->urls;
                        include __DIR__ . '/../components/entity-urls.php';
                        ?>
                    <?php endif; ?>

                </div>
            </div>

        </div>

        <!-- Back + Delete -->
        <div class="row mt-4">
            <div class="col-12 d-flex gap-3 align-items-center justify-content-between">
                <a href="/team" class="nav-icon-ux">
                    <i class="ti ti-arrow-left"></i> Team
                </a>

            </div>
        </div>

    </div>
</section>