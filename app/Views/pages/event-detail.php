<?php

/**
 * event-detail.php
 *
 * Single event detail page with inline editing for logged-in editors.
 * Edit rows use entity-edit-row pattern from edit-mode.js.
 *
 * Variables:
 *   $event           object   Event with participants, media, status
 *   $venues          array    All venues (for selector, logged in only)
 *   $allParticipants array    All participants (for add, logged in only)
 *   $categories      array    Event categories (for selector, logged in only)
 */

$saveUrl = '/events/' . $event->id . '/save';

// Split media by stage
$promoImages = [];
$videos      = [];
$gallery     = [];

foreach ($event->media ?? [] as $media) {
    if ($media->stage === 'promo' && !MediaModel::isVideo($media->media_url)) {
        $promoImages[] = $media;
    } elseif (MediaModel::isVideo($media->media_url)) {
        $videos[] = $media;
    } elseif ($media->stage === 'gallery') {
        $gallery[] = $media;
    }
}

// Helper — entity edit row (guard against redeclaration if view included twice)
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

<section class="segment light-segment container">
    <div class="container-fluid">

        <!-- Row 1: Promo image | Event details -->
        <div class="row g-5 align-items-start">

            <!-- Promo image -->
            <?php if (!empty($promoImages) || $isLoggedIn): ?>
                <div class="col-12 col-md-6">
                    <div class="media-edit-row"
                        data-entity-type="event"
                        data-entity-id="<?= $event->id ?>"
                        data-stage="promo">

                        <?php if ($isLoggedIn): ?>
                            <div class="edit-row-header">
                                <label class="edit-row-label">Promobild</label>
                                <div class="edit-row-actions">
                                    <span class="entity-feedback"></span>
                                    <label class="entity-edit-btn media-upload-btn" style="cursor:pointer;" title="Bild hinzufügen">
                                        <i class="ti ti-photo-plus"></i>
                                        <input type="file" accept="image/*" class="d-none"
                                            data-action="upload-entity-image"
                                            data-entity-type="event"
                                            data-entity-id="<?= $event->id ?>"
                                            data-stage="promo">
                                    </label>
                                    <button class="entity-edit-btn media-pencil-btn"><i class="ti ti-pencil"></i></button>
                                    <button class="entity-cancel-btn media-cancel-btn"><i class="ti ti-x"></i></button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="media-promo-content">
                            <?php if (!empty($promoImages)): ?>
                                <?php if (count($promoImages) === 1): ?>
                                    <div class="img-placeholder event-promo-img" data-media-id="<?= $promoImages[0]->id ?>">
                                        <img src="<?= htmlspecialchars($promoImages[0]->media_url) ?>"
                                            alt="<?= htmlspecialchars($event->title) ?>"
                                            class="zoomable">
                                        <?php if ($isLoggedIn): ?>
                                            <div class="image-edit-overlay">
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
                                    <?php if (!empty($promoImages[0]->caption)): ?>
                                        <small class="image-credit">
                                            <i class="ti ti-camera"></i>
                                            <?= htmlspecialchars($promoImages[0]->caption) ?>
                                        </small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <!-- Carousel -->
                                    <div id="eventPromo" class="carousel slide" data-bs-ride="carousel" data-bs-interval="10000">
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
                                                                    data-action="delete-entity-image"
                                                                    data-media-id="<?= $media->id ?>"
                                                                    data-entity-type="event"
                                                                    data-entity-id="<?= $event->id ?>">
                                                                    <i class="ti ti-trash"></i>
                                                                </button>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
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
                        </div>

                    </div>
                </div>
            <?php endif; ?>

            <!-- Event details -->
            <div class="col-12 <?= (!empty($promoImages) || $isLoggedIn) ? 'col-md-6' : '' ?>">
                <div class="section-content">

                    <?php if (!empty($event->category_label)): ?>
                        <small class="event-card__category">
                            <?= htmlspecialchars($event->category_label) ?>
                        </small>
                    <?php endif; ?>

                    <?php if ($isLoggedIn): ?>
                        <?= $editRow('Titel', 'title', $event->title ?? '', $saveUrl) ?>
                        <?= $editRow('Untertitel', 'subtitle', $event->subtitle ?? '', $saveUrl) ?>
                        <?= $editRow('Datum (YYYY-MM-DD)', 'date', $event->date ?? '', $saveUrl) ?>
                        <?= $editRow('Uhrzeit (HH:MM)', 'time', substr($event->time ?? '', 0, 5), $saveUrl) ?>

                        <!-- Venue selector -->
                        <div class="entity-select-row" data-save-url="<?= $saveUrl ?>">
                            <div class="edit-row-header">
                                <label class="edit-row-label">Veranstaltungsort</label>
                                <div class="edit-row-actions">
                                    <span class="entity-feedback"></span>
                                    <button class="entity-edit-btn"><i class="ti ti-pencil"></i></button>
                                    <button class="entity-save-btn"><i class="ti ti-check"></i></button>
                                    <button class="entity-cancel-btn"><i class="ti ti-x"></i></button>
                                </div>
                            </div>
                            <p class="entity-select-display m-2">
                                <?= !empty($event->venue_name) ? htmlspecialchars($event->venue_name) : '—' ?>
                            </p>
                            <select class="entity-field entity-select" data-field="venue_id">
                                <option value="">— kein Ort —</option>
                                <?php foreach ($venues as $venue): ?>
                                    <option value="<?= $venue->id ?>"
                                        <?= $event->venue_id == $venue->id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($venue->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Admission type selector -->
                        <div class="entity-select-row" data-save-url="<?= $saveUrl ?>">
                            <div class="edit-row-header">
                                <label class="edit-row-label">Eintritt</label>
                                <div class="edit-row-actions">
                                    <span class="entity-feedback"></span>
                                    <button class="entity-edit-btn"><i class="ti ti-pencil"></i></button>
                                    <button class="entity-save-btn"><i class="ti ti-check"></i></button>
                                    <button class="entity-cancel-btn"><i class="ti ti-x"></i></button>
                                </div>
                            </div>
                            <p class="entity-select-display m-2">
                                <?= !empty($event->admission) ? match ($event->admission) {
                                    'free'     => 'Eintritt frei',
                                    'donation' => 'Spende',
                                    'reserve'  => 'Anmeldung erforderlich',
                                    'ticket'   => 'Ticket',
                                    'external' => 'Extern',
                                    default    => '—'
                                } : '—' ?>
                            </p>
                            <select class="entity-field entity-select" data-field="admission">
                                <option value="">— keine Angabe —</option>
                                <?php foreach (['free', 'donation', 'reserve', 'ticket', 'external'] as $type): ?>
                                    <option value="<?= $type ?>" <?= $event->admission === $type ? 'selected' : '' ?>>
                                        <?= match ($type) {
                                            'free'     => 'Eintritt frei',
                                            'donation' => 'Spende',
                                            'reserve'  => 'Anmeldung erforderlich',
                                            'ticket'   => 'Ticket',
                                            'external' => 'Extern',
                                        } ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?= $editRow('Betrag / Preis', 'admission_amount', $event->admission_amount ?? '', $saveUrl) ?>
                        <?= $editRow('Ticket / Anmelde-URL', 'admission_url', $event->admission_url ?? '', $saveUrl) ?>
                        <?= $editRow('Beschreibung', 'description', $event->description ?? '', $saveUrl) ?>

                    <?php else: ?>
                        <!-- Public display -->
                        <h1><?= htmlspecialchars($event->title) ?></h1>
                        <?php if (!empty($event->subtitle)): ?>
                            <h2><?= htmlspecialchars($event->subtitle) ?></h2>
                        <?php endif; ?>

                        <div class="event-meta">
                            <?php if (!empty($event->date)): ?>
                                <span>
                                    <i class="ti ti-calendar"></i>
                                    <?= date('d.m.Y', strtotime($event->date)) ?>
                                    <?php if (!empty($event->time)): ?>
                                        · <?= date('H:i', strtotime($event->time)) ?> Uhr
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($event->venue_name)): ?>
                                <span>
                                    <i class="ti ti-map-pin"></i>
                                    <?= htmlspecialchars($event->venue_name) ?>
                                    <?php if (!empty($event->venue_street)): ?>
                                        · <?= htmlspecialchars($event->venue_street) ?>,
                                        <?= htmlspecialchars($event->venue_postcode) ?>
                                        <?= htmlspecialchars($event->venue_city) ?>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($event->description)): ?>
                            <p><?= nl2br(htmlspecialchars($event->description)) ?></p>
                        <?php endif; ?>

                        <!-- Admission display -->
                        <?php if (!empty($event->admission)): ?>
                            <div class="event-admission">
                                <?php
                                $admissionUrl    = htmlspecialchars($event->admission_url    ?? '#');
                                $admissionAmount = htmlspecialchars($event->admission_amount ?? '');
                                match ($event->admission) {
                                    'free' => print(
                                        '<span class="event-admission-label">'
                                        . '<i class="ti ti-heart-handshake"></i>'
                                        . ' Eintritt frei &middot; Spenden willkommen'
                                        . '</span>'
                                    ),
                                    'donation' => print(
                                        '<span class="event-admission-label">'
                                        . '<i class="ti ti-heart-handshake"></i>'
                                        . ' Eintritt frei &middot; Spenden willkommen'
                                        . ($admissionAmount ? ' ab ' . $admissionAmount : '')
                                        . '</span>'
                                    ),
                                    'reserve' => print(
                                        '<div class="admission-action">'
                                        . '<span class="event-admission-label">'
                                        . '<i class="ti ti-ticket"></i> Anmeldung erforderlich'
                                        . '</span>'
                                        . '<a href="' . $admissionUrl . '" class="btn-section">'
                                        . '<i class="ti ti-ticket"></i> Jetzt anmelden'
                                        . '</a></div>'
                                    ),
                                    'ticket' => print(
                                        '<div class="admission-action">'
                                        . '<span class="event-admission-label">'
                                        . '<i class="ti ti-ticket"></i> Tickets'
                                        . ($admissionAmount ? ': ' . $admissionAmount : '')
                                        . '</span>'
                                        . '<a href="' . $admissionUrl . '" target="_blank" class="btn-section">'
                                        . '<i class="ti ti-ticket"></i> Tickets kaufen'
                                        . '</a></div>'
                                    ),
                                    'external' => print(
                                        '<a href="' . $admissionUrl . '" target="_blank" class="btn-section">'
                                        . '<i class="ti ti-external-link"></i> Mehr Informationen'
                                        . '</a>'
                                    ),
                                    default => null,
                                }; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Participants -->
                    <div class="event-participants participants-edit-row">
                        <?php if ($isLoggedIn): ?>
                            <div class="edit-row-header">
                                <label class="edit-row-label">Mitwirkende</label>
                                <div class="edit-row-actions">
                                    <span class="entity-feedback"></span>
                                    <button class="entity-edit-btn"><i class="ti ti-pencil"></i></button>
                                    <button class="entity-save-btn"><i class="ti ti-check"></i></button>
                                    <button class="entity-cancel-btn"><i class="ti ti-x"></i></button>
                                </div>
                            </div>
                        <?php else: ?>
                            <h3>Mitwirkende</h3>
                        <?php endif; ?>

                        <?php if (!empty($event->participants)): ?>
                            <div class="event-participant-list p-2">
                                <?php foreach ($event->participants as $participant): ?>
                                    <div class="event-participant-item">
                                        <?php if ($isLoggedIn): ?>
                                            <button class="entity-remove-btn border-0"
                                                data-action="remove-participant"
                                                data-event-id="<?= $event->id ?>"
                                                data-participant-id="<?= $participant->id ?>">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                        <a href="/kuenstlerinnen/<?= htmlspecialchars($participant->slug) ?>">
                                            <?= htmlspecialchars($participant->displayName) ?>
                                            <?php if (!empty($participant->field)): ?>
                                                · <span class="participant-field"><?= htmlspecialchars($participant->field) ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($isLoggedIn): ?>
                            <div class="add-participant-wrap p-2">
                                <select id="participant-select-<?= $event->id ?>" class="entity-select">
                                    <option value="">— Mitwirkende:n hinzufügen —</option>
                                    <?php foreach ($allParticipants as $p): ?>
                                        <option value="<?= $p->id ?>">
                                            <?= htmlspecialchars(ParticipantModel::displayName($p)) ?>
                                            <?= !empty($p->field) ? ' · ' . htmlspecialchars($p->field) : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="entity-edit-btn"
                                    data-action="add-participant"
                                    data-event-id="<?= $event->id ?>"
                                    data-select-id="participant-select-<?= $event->id ?>">
                                    <i class="ti ti-plus"></i>
                                </button>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

        </div>
    </div>

    </div>

    <!-- Row 2: Review -->
    <hr>
    <div class="row g-5 align-items-start event-review-row">
        <div class="col-12 <?= !empty($videos) ? 'col-md-8' : '' ?>">
            <?php if ($isLoggedIn): ?>
                <?= $editRow('Rückblick', 'review', $event->review ?? '', $saveUrl) ?>
            <?php elseif (!empty($event->review)): ?>

                <div class="event-review">
                    <h3>Rückblick</h3>

                    <p><?= nl2br(htmlspecialchars($event->review)) ?></p>
                </div>
            <?php endif; ?>
            <hr>
        </div>

        <?php if (!empty($videos)): ?>
            <div class="col-12 col-md-4">
                <?php foreach ($videos as $video): ?>
                    <div class="event-media-item">
                        <video src="<?= htmlspecialchars($video->media_url) ?>" controls></video>
                        <?php if (!empty($video->caption)): ?>
                            <small><?= htmlspecialchars($video->caption) ?></small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Row 3: Gallery -->
    <?php if (!empty($gallery) || $isLoggedIn): ?>
        <div class="media-edit-row mt-2"
            data-entity-type="event"
            data-entity-id="<?= $event->id ?>"
            data-stage="gallery">

            <?php if ($isLoggedIn): ?>
                <div class="edit-row-header">
                    <label class="edit-row-label">Galerie</label>
                    <div class="edit-row-actions">
                        <span class="entity-feedback"></span>
                        <button class="section-control-btn gallery-btn-caption">
                            <i class="ti ti-text-caption"></i> Caption
                        </button>
                        <button class="section-control-btn gallery-btn-credit">
                            <i class="ti ti-camera"></i> Credit
                        </button>
                        <label class="entity-edit-btn gallery-select-all" title="Alle auswählen" style="cursor:pointer;">
                            <input type="checkbox" class="gallery-checkbox-all" style="margin:0;">
                        </label>
                        <label class="entity-edit-btn media-upload-btn" style="cursor:pointer;" title="Bild hinzufügen">
                            <i class="ti ti-photo-plus"></i>
                            <input type="file" accept="image/*" class="d-none"
                                data-action="upload-entity-image"
                                data-entity-type="event"
                                data-entity-id="<?= $event->id ?>"
                                data-stage="gallery">
                        </label>
                        <button class="entity-edit-btn media-pencil-btn"><i class="ti ti-pencil"></i></button>
                        <button class="entity-cancel-btn media-cancel-btn"><i class="ti ti-x"></i></button>
                    </div>
                </div>

                <div class="media-upload-zone">
                    <div class="media-dropzone">
                        <i class="ti ti-photo-plus"></i>
                        <span>Fotos hierher ziehen oder klicken</span>
                        <input type="file" accept="image/*" multiple class="media-file-input">
                    </div>
                    <button class="section-control-btn media-upload-confirm mt-2">
                        <i class="ti ti-upload"></i> Hochladen
                    </button>
                    <div class="media-upload-progress mt-1"></div>
                </div>
            <?php elseif (!empty($gallery)): ?>

                <div class="event-review">
                    <hr>
                    <h3>Galerie</h3>
                </div>
            <?php endif; ?>

            <div class="row g-3 event-gallery media-gallery-grid p-2">
                <?php if (empty($gallery) && $isLoggedIn): ?>
                    <div class="col-12">
                        <p class="text-muted p-2">
                            <i class="ti ti-photo-off"></i>
                            Noch keine Galeriebilder — Bearbeitungsmodus aktivieren um Fotos hochzuladen.
                        </p>
                    </div>
                <?php endif; ?>

                <?php foreach ($gallery as $media): ?>
                    <div class="col-6 col-md-4 col-lg-3 gallery-item" data-media-id="<?= $media->id ?>">
                        <?php if ($isLoggedIn): ?>
                            <label class="gallery-item-checkbox">
                                <input type="checkbox" class="gallery-checkbox" value="<?= $media->id ?>">
                            </label>
                        <?php endif; ?>
                        <div class="img-placeholder event-gallery-img">
                            <img src="<?= htmlspecialchars($media->media_url) ?>"
                                alt="<?= htmlspecialchars($media->caption ?? $event->title) ?>"
                                class="zoomable">
                            <?php if ($isLoggedIn): ?>
                                <div class="image-edit-overlay">
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
                            <small class="gallery-item-meta">
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

        </div>
    <?php endif; ?>

    <!-- Media meta modal -->
    <?php if ($isLoggedIn): ?>
        <div class="media-meta-modal" id="mediaMetaModal" style="display:none;">
            <div class="media-meta-modal-inner">
                <h4 class="media-meta-modal-title">Caption</h4>
                <textarea class="media-meta-textarea" rows="4" placeholder=""></textarea>
                <div class="media-meta-modal-actions">
                    <button class="section-control-btn media-meta-cancel">Abbrechen</button>
                    <button class="section-control-btn media-meta-confirm">Speichern</button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Back + Delete -->
    <div class="row mt-4">
        <div class="col-12 d-flex gap-3 align-items-center justify-content-between">
            <a href="/veranstaltungen" class="nav-icon-ux">
                <i class="ti ti-arrow-left"></i> Veranstaltungen
            </a>
            <?php if ($isLoggedIn): ?>
                <button class="btn-section"
                    data-action="delete-event"
                    data-event-id="<?= $event->id ?>"
                    data-event-slug="<?= htmlspecialchars($event->slug) ?>">
                    <i class="ti ti-trash"></i> Veranstaltung löschen
                </button>
            <?php endif; ?>
        </div>
    </div>

    </div>
</section>