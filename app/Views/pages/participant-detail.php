<?php

/**
 * participant-detail.php
 *
 * Single participant detail page with inline editing for logged-in editors.
 * Edit rows follow the entity-edit-row pattern from edit-mode.js.
 *
 * Variables:
 *   $participant  object   Participant with urls, events
 *   $isLoggedIn   bool     From BaseController
 */

$saveUrl = '/participants/' . $participant->id . '/save';

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
?>

<section class="segment light-segment">
    <div class="container">
        <div class="row align-items-start g-5">

            <!-- Image -->
            <div class="col-12 col-md-4">

                <?php if (!empty($participant->profileImg) || $isLoggedIn): ?>
                    <div class="media-edit-row"
                        data-entity-type="participant"
                        data-entity-id="<?= $participant->id ?>"
                        data-entity-slug="<?= htmlspecialchars($participant->slug) ?>"
                        data-stage="profile"
                        data-fragment-url="/participants/<?= $participant->id ?>/profile-fragment">

                        <?php if ($isLoggedIn): ?>
                            <div class="edit-row-header">
                                <label class="edit-row-label">Foto</label>
                                <div class="edit-row-actions">
                                    <span class="entity-feedback"></span>
                                    <label class="entity-edit-btn media-upload-btn" style="cursor:pointer;" title="Foto hochladen">
                                        <i class="ti ti-photo-plus"></i>
                                        <input type="file" accept="image/*" class="d-none"
                                            data-action="upload-entity-image"
                                            data-entity-type="participant"
                                            data-entity-id="<?= $participant->id ?>"
                                            data-entity-slug="<?= htmlspecialchars($participant->slug) ?>"
                                            data-stage="profile">
                                    </label>
                                    <button class="entity-edit-btn media-pencil-btn"><i class="ti ti-pencil"></i></button>
                                    <button class="entity-cancel-btn media-cancel-btn"><i class="ti ti-x"></i></button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="media-promo-content">
                            <?php
                            $entity     = $participant;
                            $entityType = 'participant';
                            $profileImg = $participant->profileImg ?? null;
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

                        <!-- Type selector -->
                        <div class="entity-select-row" data-save-url="<?= $saveUrl ?>" data-participant-type-row>
                            <div class="edit-row-header">
                                <label class="edit-row-label">Typ</label>
                                <div class="edit-row-actions">
                                    <span class="entity-feedback"></span>
                                    <button class="entity-edit-btn"><i class="ti ti-pencil"></i></button>
                                    <button class="entity-save-btn"><i class="ti ti-check"></i></button>
                                    <button class="entity-cancel-btn"><i class="ti ti-x"></i></button>
                                </div>
                            </div>
                            <p class="entity-select-display m-2">
                                <?= !empty($participant->type) ? htmlspecialchars(ucfirst($participant->type)) : '—' ?>
                            </p>
                            <select class="entity-field entity-select" data-field="type">
                                <option value="">— bitte wählen —</option>
                                <?php foreach (['individual', 'ensemble', 'orchestra'] as $t): ?>
                                    <option value="<?= $t ?>" <?= ($participant->type ?? '') === $t ? 'selected' : '' ?>>
                                        <?= match ($t) {
                                            'individual' => 'Person',
                                            'ensemble'   => 'Ensemble',
                                            'orchestra'  => 'Orchester',
                                        } ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Person-only fields: title + last name hidden for ensemble/orchestra -->
                        <div data-person-only <?= !empty($participant->type) && $participant->type !== 'individual' ? 'style="display:none"' : '' ?>>
                            <?= $editRow('Titel (Mag., Dr., …)', 'title',     $participant->title     ?? '', $saveUrl) ?>
                            <?= $editRow('Nachname',             'last_name', $participant->last_name ?? '', $saveUrl) ?>
                        </div>

                        <?= $editRow('Vorname / Ensemblename', 'first_name', $participant->first_name ?? '', $saveUrl) ?>
                        <?= $editRow('Fach / Instrument',      'field',      $participant->field      ?? '', $saveUrl) ?>
                        <?= $editRow('Kurzbiografie',          'bio',        $participant->bio        ?? '', $saveUrl) ?>

                    <?php else: ?>

                        <!-- Public display -->
                        <h1><?= htmlspecialchars($participant->displayName) ?></h1>

                        <?php if (!empty($participant->field)): ?>
                            <h2><?= htmlspecialchars($participant->field) ?></h2>
                        <?php endif; ?>

                        <?php if (!empty($participant->type) && $participant->type !== 'individual'): ?>
                            <small><?= htmlspecialchars(ucfirst($participant->type)) ?></small>
                        <?php endif; ?>

                        <?php if (!empty($participant->bio)): ?>
                            <p><?= nl2br(htmlspecialchars($participant->bio)) ?></p>
                        <?php endif; ?>

                    <?php endif; ?>

                    <!-- Links -->
                    <?php if (!empty($participant->urls) || $isLoggedIn): ?>
                        <?php
                        $entityType = 'participant';
                        $entityId   = $participant->id;
                        $urls       = $participant->urls;
                        include __DIR__ . '/../components/entity-urls.php';
                        ?>
                    <?php endif; ?>

                    <!-- Events -->
                    <?php if (!empty($participant->events)): ?>
                        <div class="participant-events mt-3">
                            <h3>Veranstaltungen</h3>
                            <ul class="participant-events-list">
                                <?php foreach ($participant->events as $event): ?>
                                    <li>
                                        <a href="/veranstaltungen/<?= htmlspecialchars($event->slug) ?>">
                                            <?= htmlspecialchars($event->title) ?>
                                            <?php if (!empty($event->date)): ?>
                                                <small><?= date('d.m.Y', strtotime($event->date)) ?></small>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>

        <!-- Back + Delete -->
        <div class="row mt-4">
            <div class="col-12 d-flex gap-3 align-items-center justify-content-between">
                <a href="/kuenstlerinnen" class="nav-icon-ux">
                    <i class="ti ti-arrow-left"></i> Künstler:innen
                </a>
                <?php if ($isLoggedIn): ?>
                    <button class="btn-section"
                        data-action="delete-participant"
                        data-participant-id="<?= $participant->id ?>">
                        <i class="ti ti-trash"></i> Künstler:in löschen
                    </button>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>