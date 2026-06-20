<?php

/**
 * partials/promo-media.php
 *
 * Renders an entity's promo media: single image, carousel (2+ images),
 * or an upload placeholder when none exist and the viewer can edit.
 *
 * Extracted from event-detail.php so the same rendering logic can be
 * reused anywhere promo media needs to be shown (event detail page,
 * a future archive/listing flyer display, a small fragment endpoint
 * used to rebuild the carousel in-place after a delete, etc.)
 *
 * Required variables (passed in via include or render() data):
 *   $event        object   must have ->id, ->title
 *   $promoImages  array    of media objects (media_url, id, caption, credit)
 *   $isLoggedIn   bool
 *
 * Caller is responsible for wrapping this in whatever container/edit-row
 * markup it needs (see event-detail.php for the full media-edit-row wrapper).
 */
?>
<?php if (!empty($promoImages)): ?>
    <?php if (count($promoImages) === 1): ?>
        <div class="img-placeholder event-promo-img" data-media-id="<?= $promoImages[0]->id ?>">
            <img src="<?= htmlspecialchars($promoImages[0]->media_url) ?>"
                alt="<?= htmlspecialchars($event->title) ?>"
                class="zoomable">
            <?php if ($isLoggedIn): ?>
                <div class="image-edit-overlay">
                    <button class="section-control-btn"
                        data-action="edit-image-caption"
                        data-media-id="<?= $promoImages[0]->id ?>"
                        data-caption="<?= htmlspecialchars($promoImages[0]->caption ?? '') ?>">
                        <i class="ti ti-text-caption"></i>
                    </button>
                    <button class="section-control-btn"
                        data-action="edit-image-credit"
                        data-media-id="<?= $promoImages[0]->id ?>"
                        data-credit="<?= htmlspecialchars($promoImages[0]->credit ?? '') ?>">
                        <i class="ti ti-camera"></i>
                    </button>
                    <button class="section-control-btn"
                        data-action="delete-entity-image"
                        data-media-id="<?= $promoImages[0]->id ?>"
                        data-entity-type="event"
                        data-entity-id="<?= $event->id ?>">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            <?php endif; ?>
        </div>
        <?php if (!empty($promoImages[0]->caption) || !empty($promoImages[0]->credit)): ?>
            <small class="image-meta">
                <?php if (!empty($promoImages[0]->caption)): ?>
                    <span><?= htmlspecialchars($promoImages[0]->caption) ?></span>
                <?php endif; ?>
                <?php if (!empty($promoImages[0]->credit)): ?>
                    <span class="image-credit"><i class="ti ti-camera"></i> <?= htmlspecialchars($promoImages[0]->credit) ?></span>
                <?php endif; ?>
            </small>
        <?php endif; ?>
    <?php else: ?>
        <!-- Carousel -->
        <div id="eventPromo" class="carousel slide"
            data-bs-ride="<?= $isLoggedIn ? 'false' : 'carousel' ?>"
            data-bs-interval="10000">
            <div class="carousel-inner">
                <?php foreach ($promoImages as $i => $media): ?>
                    <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>"
                        data-media-id="<?= $media->id ?>">
                        <div class="img-placeholder event-promo-img">
                            <img src="<?= htmlspecialchars($media->media_url) ?>"
                                alt="<?= htmlspecialchars($media->caption ?? $event->title) ?>"
                                class="zoomable">
                            <?php if ($isLoggedIn): ?>
                                <div class="image-edit-overlay">
                                    <button class="section-control-btn"
                                        data-action="edit-image-caption"
                                        data-media-id="<?= $media->id ?>"
                                        data-caption="<?= htmlspecialchars($media->caption ?? '') ?>">
                                        <i class="ti ti-text-caption"></i>
                                    </button>
                                    <button class="section-control-btn"
                                        data-action="edit-image-credit"
                                        data-media-id="<?= $media->id ?>"
                                        data-credit="<?= htmlspecialchars($media->credit ?? '') ?>">
                                        <i class="ti ti-camera"></i>
                                    </button>
                                    <button class="section-control-btn"
                                        data-action="delete-entity-image"
                                        data-media-id="<?= $media->id ?>"
                                        data-entity-type="event"
                                        data-entity-id="<?= $event->id ?>">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($media->caption) || !empty($media->credit)): ?>
                            <small class="image-meta">
                                <?php if (!empty($media->caption)): ?>
                                    <span><?= htmlspecialchars($media->caption) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($media->credit)): ?>
                                    <span class="image-credit"><i class="ti ti-camera"></i> <?= htmlspecialchars($media->credit) ?></span>
                                <?php endif; ?>
                            </small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button"
                data-bs-target="#eventPromo" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button"
                data-bs-target="#eventPromo" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
            <div class="carousel-indicators">
                <?php foreach ($promoImages as $i => $media): ?>
                    <button type="button" data-bs-target="#eventPromo"
                        data-bs-slide-to="<?= $i ?>"
                        <?= $i === 0 ? 'class="active"' : '' ?>></button>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
<?php else: ?>
    <!-- No promo image -->
    <div class="img-placeholder event-promo-img media-placeholder">
        <i class="ti ti-music"></i>
        <?php if ($isLoggedIn): ?>
            <label class="section-control-btn placeholder-upload-btn" style="cursor:pointer;">
                <i class="ti ti-photo-plus"></i> Promobild hochladen
                <input type="file" accept="image/*" class="d-none"
                    data-action="upload-entity-image"
                    data-entity-type="event"
                    data-entity-id="<?= $event->id ?>"
                    data-stage="promo">
            </label>
        <?php endif; ?>
    </div>
<?php endif; ?>