<?php

/**
 * components/profile-img.php
 *
 * Renders a single entity profile image with edit overlay.
 * Entity-agnostic — reusable for participants, team members,
 * and any future entity with a single portrait-style image.
 *
 * Follows the same structure as promo-media.php for events.
 * Caller is responsible for wrapping this in a media-edit-row
 * with the correct data-entity-type / data-entity-id / data-stage.
 *
 * Required variables:
 *   $entity      object        must have ->id, ->displayName (or equivalent)
 *   $entityType  string        e.g. 'participant', 'team'
 *   $profileImg  object|null   media row from entity_media (stage='profile')
 *   $isLoggedIn  bool
 */
?>

<?php if (!empty($profileImg)): ?>

    <div class="img-placeholder portrait-img" data-media-id="<?= $profileImg->id ?>">
        <img src="<?= htmlspecialchars($profileImg->media_url) ?>"
            alt="<?= htmlspecialchars($entity->displayName ?? '') ?>">
        <?php if ($isLoggedIn): ?>
            <div class="image-edit-overlay">
                <button class="section-control-btn"
                    data-action="edit-image-credit"
                    data-media-id="<?= $profileImg->id ?>"
                    data-credit="<?= htmlspecialchars($profileImg->credit ?? '') ?>">
                    <i class="ti ti-camera"></i>
                </button>
                <button class="section-control-btn"
                    data-action="delete-entity-image"
                    data-media-id="<?= $profileImg->id ?>"
                    data-entity-type="<?= htmlspecialchars($entityType) ?>"
                    data-entity-id="<?= $entity->id ?>">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($profileImg->credit)): ?>
        <small class="image-credit">
            <i class="ti ti-camera"></i> <?= htmlspecialchars($profileImg->credit) ?>
        </small>
    <?php endif; ?>

<?php else: ?>

    <div class="img-placeholder portrait-img media-placeholder">
        <i class="ti ti-user"></i>
        <?php if ($isLoggedIn): ?>
            <label class="section-control-btn placeholder-upload-btn" style="cursor:pointer;">
                <i class="ti ti-photo-plus"></i> Foto hochladen
                <input type="file" accept="image/*" class="d-none"
                    data-action="upload-entity-image"
                    data-entity-type="<?= htmlspecialchars($entityType) ?>"
                    data-entity-id="<?= $entity->id ?>"
                    data-entity-slug="<?= htmlspecialchars($entity->slug ?? '') ?>"
                    data-stage="profile">
            </label>
        <?php endif; ?>
    </div>

<?php endif; ?>