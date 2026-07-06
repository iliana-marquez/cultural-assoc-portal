<?php

/**
 * contributor-card.php
 *
 * Reusable contributor card — used in both edit mode and public view.
 * Required: $contributor, $isLoggedIn
 * Optional: $saveUrl (required in edit mode)
 */
?>

<div class="card mb-3 contributor-card <?= $isLoggedIn && ($contributor->status === 'draft') ? 'contributor-card--draft' : '' ?>"
    data-contributor-id="<?= $contributor->id ?>">
    <div class="card-body">

        <?php if ($isLoggedIn): ?>
            <!-- Status + actions -->
            <div class="contributor-card-header mb-2 justify-content-end">
                <?php if ($contributor->status === 'draft'): ?>
                    <span class="status-badge status-badge--draft"></span>
                <?php endif; ?>
                <div class="contributor-card-actions">
                    <?php if ($contributor->status === 'draft'): ?>
                        <button class="entity-edit-btn contributor-publish-btn"
                            data-id="<?= $contributor->id ?>">
                            <i class="ti ti-eye"></i> Veröffentlichen
                        </button>
                        <span>|</span>
                        <button class="entity-remove-btn contributor-delete-btn"
                            data-id="<?= $contributor->id ?>">
                            <i class="ti ti-trash"></i>
                        </button>
                    <?php else: ?>
                        <button class="entity-edit-btn contributor-unpublish-btn"
                            data-id="<?= $contributor->id ?>">
                            <i class="ti ti-eye-off"></i> Als entwurf markieren
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="row align-items-center">

            <!-- Logo -->
            <div class="col-12 col-md-4 col-lg-3">
                <?php if ($isLoggedIn): ?>
                    <div class="media-edit-row"
                        data-entity-type="contributor"
                        data-entity-id="<?= $contributor->id ?>"
                        data-entity-slug=""
                        data-stage="profile"
                        data-fragment-url="/contributors/<?= $contributor->id ?>/profile-fragment">

                        <div class="edit-row-header">
                            <label class="edit-row-label">Logo</label>
                            <div class="edit-row-actions">
                                <span class="entity-feedback"></span>
                                <label class="entity-edit-btn media-upload-btn" style="cursor:pointer;" title="Logo hochladen">
                                    <i class="ti ti-photo-plus"></i>
                                    <input type="file" accept="image/*" class="d-none"
                                        data-action="upload-entity-image"
                                        data-entity-type="contributor"
                                        data-entity-id="<?= $contributor->id ?>"
                                        data-entity-slug=""
                                        data-stage="profile">
                                </label>
                                <button class="entity-edit-btn media-pencil-btn"><i class="ti ti-pencil"></i></button>
                                <button class="entity-cancel-btn media-cancel-btn"><i class="ti ti-x"></i></button>
                            </div>
                        </div>

                        <div class="media-promo-content">
                            <?php
                            $entity     = (object) [
                                'id'          => $contributor->id,
                                'displayName' => $contributor->name,
                                'slug'        => null,
                            ];
                            $profileImg = $contributor->profileImg ?? null;
                            $entityType = 'contributor';
                            require __DIR__ . '/profile-img.php';
                            ?>
                        </div>
                    </div>
                <?php elseif (!empty($contributor->profileImg)): ?>
                    <img src="<?= htmlspecialchars($contributor->profileImg->media_url) ?>"
                        alt="<?= htmlspecialchars($contributor->name) ?>"
                        class="img-fluid">
                <?php endif; ?>
            </div>

            <!-- Content -->
            <div class="col-12 col-md-8 col-lg-9">
                <?php if ($isLoggedIn): ?>
                    <?= editRow('Name', 'name', $contributor->name ?? '', $saveUrl) ?>

                    <div class="entity-select-row" data-save-url="<?= $saveUrl ?>">
                        <div class="edit-row-header">
                            <label class="edit-row-label">Kategorie</label>
                            <div class="edit-row-actions">
                                <span class="entity-feedback"></span>
                                <button class="entity-edit-btn"><i class="ti ti-pencil"></i></button>
                                <button class="entity-save-btn"><i class="ti ti-check"></i></button>
                                <button class="entity-cancel-btn"><i class="ti ti-x"></i></button>
                            </div>
                        </div>
                        <?php
                        $typeOptions = [
                            ''              => '— Kategorie wählen —',
                            'partner'       => 'Partner',
                            'foerderer'     => 'Förderer',
                            'unterstuetzer' => 'Unterstützer',
                            'institution'   => 'Institution',
                        ];
                        ?>
                        <p class="entity-select-display m-2">
                            <?= htmlspecialchars($typeOptions[$contributor->type ?? ''] ?? ucfirst($contributor->type ?? '—')) ?>
                        </p>
                        <select class="entity-field entity-select" data-field="type">
                            <?php foreach ($typeOptions as $val => $label): ?>
                                <option value="<?= $val ?>" <?= ($contributor->type ?? '') === $val ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?= editRow('Beschreibung', 'description', $contributor->description ?? '', $saveUrl) ?>

                    <?php
                    $hideUrlsLabel = true;
                    $entityType = 'contributor';
                    $entityId   = $contributor->id;
                    $urls       = $contributor->urls ?? [];
                    require __DIR__ . '/entity-urls.php';
                    ?>

                <?php else: ?>
                    <h4 class="h5 mb-2"><?= htmlspecialchars($contributor->name) ?></h4>
                    <?php if (!empty($contributor->description)): ?>
                        <p class="small mb-2"><?= htmlspecialchars($contributor->description) ?></p>
                    <?php endif; ?>
                    <?php
                    $hideUrlsLabel = true;
                    $entityType = 'contributor';
                    $entityId   = $contributor->id;
                    $urls       = $contributor->urls ?? [];
                    require __DIR__ . '/entity-urls.php';
                    ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>