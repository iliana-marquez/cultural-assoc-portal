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

// Conditionals to go back either to programm or achive pages
$isUpcoming = strtotime($event->date) >= strtotime('today');
$backUrl    = $isUpcoming
    ? '/programm'
    : '/archiv?year=' . date('Y', strtotime($event->date));
$backLabel  = $isUpcoming ? 'Programm' : 'Archiv';

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

<section class="segment light-segment">
    <div class="container px-3">
        <?php if ($isLoggedIn): ?>
            <?php
            $isCancelled = !empty($event->cancelled_at);
            $isDraft     = ($event->status ?? 'draft') === 'draft';
            $isUpcoming  = !empty($event->date) && strtotime($event->date) >= strtotime('today');
            ?>
            <div class="event-status-bar">
                <?php if ($isCancelled): ?>
                    <span class="event-status-chip event-status-chip--cancelled">
                        <i class="ti ti-masks-theater-off"></i> Diese Veranstaltung wurde abgesagt.
                    </span>
                <?php elseif ($isDraft): ?>
                    <button class="btn-section btn-section--primary"
                        data-action="publish-event"
                        data-event-id="<?= $event->id ?>">
                        <i class="ti ti-upload"></i> Veröffentlichen
                    </button>
                    <i class="ti ti-arrow-narrow-left"></i>
                    <span class="event-status-chip event-status-chip--draft">
                        <strong>ENTWURF <i class="ti ti-mood-off"></i></strong>
                    </span> <i class="ti ti-arrow-narrow-right"></i>
                    <button class="btn-section btn-section--danger"
                        data-action="delete-event"
                        data-event-id="<?= $event->id ?>"
                        data-event-slug="<?= htmlspecialchars($event->slug) ?>">
                        <i class="ti ti-trash"></i> Löschen
                    </button>
                <?php else: ?>
                    <button class="btn-section"
                        data-action="unpublish-event"
                        data-event-id="<?= $event->id ?>">
                        <i class="ti ti-arrow-back-up"></i> Als Entwurf zurücksetzen
                    </button>
                    <i class="ti ti-arrow-narrow-left"></i>
                    <span class="event-status-chip event-status-chip--published">
                        <strong>VERÖFFENLICHT <i class="ti ti-mood-check"></i></strong>
                    </span>

                    <?php if ($isUpcoming): ?>
                        <i class="ti ti-arrow-narrow-right"></i>
                        <button class="btn-section btn-section--danger"
                            data-action="cancel-event"
                            data-event-id="<?= $event->id ?>">
                            Absagen
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>


        <!-- Row 1: Promo image | Event details -->
        <div class="row gx-4 gy-4 align-items-start">

            <!-- Promo image -->
            <?php if (!empty($promoImages) || $isLoggedIn): ?>
                <div class="col-12 col-md-6">
                    <div class="media-edit-row"
                        data-entity-type="event"
                        data-entity-id="<?= $event->id ?>"
                        data-entity-slug="<?= htmlspecialchars($event->slug) ?>"
                        data-stage="promo"
                        data-fragment-url="/events/<?= $event->id ?>/promo-fragment">

                        <?php if ($isLoggedIn): ?>
                            <div class="edit-row-header">
                                <label class="edit-row-label">
                                    Promobild
                                    <span class="media-count">(<?= count($promoImages) ?>)</span>
                                </label>
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
                            <?php include __DIR__ . '/../components/event/promo-media.php'; ?>
                        </div>

                    </div>
                </div>
            <?php endif; ?>

            <!-- Event details -->
            <div class="col-12 <?= (!empty($promoImages) || $isLoggedIn) ? 'col-md-6' : '' ?>">
                <div class="section-content">

                    <?php if ($isLoggedIn): ?>
                        <!-- Category selector -->
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
                            <p class="entity-select-display m-2">
                                <?= !empty($event->category_label) ? htmlspecialchars($event->category_label) : '—' ?>
                            </p>
                            <select class="entity-field entity-select" data-field="category_id">
                                <option value="">— keine Kategorie —</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat->id ?>"
                                        <?= $event->category_id == $cat->id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat->label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?= $editRow('Titel', 'title', $event->title ?? '', $saveUrl) ?>
                        <?= $editRow('Untertitel', 'subtitle', $event->subtitle ?? '', $saveUrl) ?>
                        <?= $editRow('Datum (YYYY-MM-DD)', 'date', $event->date ?? '', $saveUrl) ?>
                        <?= $editRow('Uhrzeit (HH:MM)', 'time', substr($event->time ?? '', 0, 5), $saveUrl) ?>

                        <!-- Venue -->
                        <?php
                        $entityType = 'event';
                        $entityId   = $event->id;
                        include __DIR__ . '/../components/entity-venues.php';
                        ?>

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
                        <?= $editRow('Ticket / Anmeldeldung (Link: https://... | Email: mailto:...)', 'admission_url', $event->admission_url ?? '', $saveUrl) ?>
                        <?= $editRow('Beschreibung', 'description', $event->description ?? '', $saveUrl) ?>

                    <?php else: ?>
                        <!-- Public display -->
                        <?php if (!empty($event->category_label)): ?>
                            <small class="event-card__category">
                                <?= htmlspecialchars($event->category_label) ?>
                            </small>
                        <?php endif; ?>
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
                                <?php
                                $venueMapUrl = $event->venue->map_url     ?? null;
                                $venueWebUrl = $event->venue->website_url ?? null;
                                ?>
                                <span>
                                    <i class="ti ti-map-pin"></i>
                                    <?php if ($venueWebUrl): ?>
                                        <a href="<?= htmlspecialchars($venueWebUrl) ?>" target="_blank" rel="noopener noreferrer">
                                            <?= htmlspecialchars($event->venue_name) ?>
                                        </a>
                                    <?php else: ?>
                                        <?= htmlspecialchars($event->venue_name) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($event->venue_street)): ?>
                                        <?php if ($venueMapUrl): ?>
                                            · <a href="<?= htmlspecialchars($venueMapUrl) ?>" target="_blank" rel="noopener noreferrer">
                                                <?= htmlspecialchars($event->venue_street) ?>,
                                                <?= htmlspecialchars($event->venue_postcode) ?>
                                                <?= htmlspecialchars($event->venue_city) ?>
                                            </a>
                                        <?php else: ?>
                                            · <?= htmlspecialchars($event->venue_street) ?>,
                                            <?= htmlspecialchars($event->venue_postcode) ?>
                                            <?= htmlspecialchars($event->venue_city) ?>
                                        <?php endif; ?>
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
                                $admissionUrl      = htmlspecialchars($event->admission_url    ?? '#');
                                $admissionAmount   = htmlspecialchars($event->admission_amount ?? '');
                                $isUpcomingEvent   = !empty($event->date) && strtotime($event->date) >= strtotime('today');
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
                                        . '<i class="ti ti-user-edit"></i> Anmeldung erforderlich'
                                        . '</span>'
                                        . ($isUpcomingEvent && $event->admission_url
                                            ? '<a href="' . $admissionUrl . '" class="btn-section">Jetzt anmelden</a>'
                                            : '')
                                        . '</div>'
                                    ),
                                    'ticket' => print(
                                        '<div class="admission-action">'
                                        . '<span class="event-admission-label">'
                                        . '<i class="ti ti-ticket"></i> Tickets'
                                        . ($admissionAmount ? ': ' . $admissionAmount : '')
                                        . '</span>'
                                        . ($isUpcomingEvent && $event->admission_url
                                            ? '<a href="' . $admissionUrl . '" target="_blank" class="btn-section">Tickets kaufen</a>'
                                            : '')
                                        . '</div>'
                                    ),
                                    'external' => print(
                                        $isUpcomingEvent && $event->admission_url
                                        ? '<a href="' . $admissionUrl . '" target="_blank" class="btn-section">'
                                        . '<i class="ti ti-external-link"></i> Mehr Informationen'
                                        . '</a>'
                                        : ''
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
                        <?php elseif (!empty($event->participants)): ?>
                            <h3>Mitwirkende</h3>
                        <?php endif; ?>

                        <?php if (!empty($event->participants)): ?>
                            <?php
                            $individuals = array_filter($event->participants, fn($p) => ($p->type ?? 'individual') === 'individual');
                            $groups      = array_filter($event->participants, fn($p) => ($p->type ?? 'individual') !== 'individual');
                            ?>

                            <div class="event-participant-list p-2">

                                <?php if (!empty($individuals)): ?>
                                    <?php foreach ($individuals as $participant): ?>
                                        <div class="event-participant-item">
                                            <?php if ($isLoggedIn): ?>
                                                <button class="entity-remove-btn border-0"
                                                    data-action="remove-participant"
                                                    data-event-id="<?= $event->id ?>"
                                                    data-participant-id="<?= $participant->id ?>">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($isLoggedIn || ($participant->status ?? 'published') === 'published'): ?>
                                                <i class="ti ti-arrow-narrow-right"></i>
                                                <a href="/kuenstlerinnen/<?= htmlspecialchars($participant->slug) ?>" class="inline-link">
                                                    <?= htmlspecialchars($participant->displayName) ?>
                                                    <?php if (!empty($participant->field)): ?>
                                                        · <span class="participant-field"><?= htmlspecialchars($participant->field) ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($isLoggedIn && ($participant->status ?? 'published') !== 'published'): ?>
                                                        <small class="text-muted"> - Entwurf</small>
                                                    <?php endif; ?>
                                                </a>
                                            <?php else: ?>
                                                <i class="ti ti-arrow-narrow-right"></i>
                                                <span class="text-muted">
                                                    <?= htmlspecialchars($participant->displayName) ?>
                                                    <?php if (!empty($participant->field)): ?>
                                                        · <span class="participant-field"><?= htmlspecialchars($participant->field) ?></span>
                                                    <?php endif; ?>
                                                    <small> - bald veröffentlicht</small>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if (!empty($groups)): ?>
                                    <small class="text-uppercase event-participant-item">
                                        <?= count($groups) === 1 ? 'Ensemble' : 'Ensembles' ?>
                                        <hr>
                                    </small>
                                    <?php foreach ($groups as $participant): ?>
                                        <div class="event-participant-item">
                                            <?php if ($isLoggedIn): ?>
                                                <button class="entity-remove-btn border-0"
                                                    data-action="remove-participant"
                                                    data-event-id="<?= $event->id ?>"
                                                    data-participant-id="<?= $participant->id ?>">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                            <i class="ti ti-arrow-narrow-right"></i>
                                            <?php if ($isLoggedIn || ($participant->status ?? 'published') === 'published'): ?>
                                                <a href="/kuenstlerinnen/<?= htmlspecialchars($participant->slug) ?>" class="inline-link">
                                                    <?= htmlspecialchars($participant->displayName) ?>
                                                    <?php if (!empty($participant->field)): ?>
                                                        · <span class="participant-field"><?= htmlspecialchars($participant->field) ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($isLoggedIn && ($participant->status ?? 'published') !== 'published'): ?>
                                                        <small class="text-muted"> - Entwurf</small>
                                                    <?php endif; ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">
                                                    <?= htmlspecialchars($participant->displayName) ?>
                                                    <?php if (!empty($participant->field)): ?>
                                                        · <span class="participant-field"><?= htmlspecialchars($participant->field) ?></span>
                                                    <?php endif; ?>
                                                    <small> - bald veröffentlicht</small>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                            </div>

                        <?php elseif ($isLoggedIn): ?>
                            <div class="event-participant-list p-2">
                                <p class="text-muted p-2 mb-0">
                                    <i class="ti ti-users-group"></i>
                                    Noch keine Mitwirkenden
                                </p>
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

                    <!-- URLs -->
                    <?php
                    $entityType = 'event';
                    $entityId   = $event->id;
                    $urls       = $event->urls;
                    include __DIR__ . '/../components/entity-urls.php';
                    ?>

                </div>
            </div>

        </div>

        <!-- Row 2: Review -->
        <div class="row gx-4 gy-4 align-items-start event-review-row">
            <div class="col-12 <?= !empty($videos) ? 'col-md-8' : '' ?>">
                <?php if ($isLoggedIn): ?>
                    <?= $editRow('Rückblick', 'review', $event->review ?? '', $saveUrl) ?>
                <?php elseif (!empty($event->review)): ?>

                    <div class="event-review">
                        <hr>
                        <h3>Rückblick</h3>

                        <p><?= nl2br(htmlspecialchars($event->review)) ?></p>
                    </div>
                <?php endif; ?>
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
            <div class="row mt-4">
                <div class="col-12">
                    <div class="media-edit-row mt-2"
                        data-entity-type="event"
                        data-entity-id="<?= $event->id ?>"
                        data-entity-slug="<?= htmlspecialchars($event->slug) ?>"
                        data-stage="gallery">

                        <?php if ($isLoggedIn): ?>
                            <div class="edit-row-header">
                                <label class="edit-row-label">Galerie</label>
                                <div class="edit-row-actions align-items-center">
                                    <span class="entity-feedback"></span>
                                    <button class="section-control-btn gallery-btn-caption">
                                        <i class="ti ti-text-caption"></i> Caption
                                    </button>
                                    <button class="section-control-btn gallery-btn-credit">
                                        <i class="ti ti-camera"></i> Credit
                                    </button>
                                    <button class="section-control-btn gallery-btn-delete">
                                        <i class="ti ti-trash"></i> Löschen
                                    </button>
                                    <label class="entity-edit-btn gallery-select-all" title="Alle auswählen" style="cursor:pointer;">
                                        <span style="margin-right:0.5rem;">select all</span>
                                        <input type="checkbox" class="gallery-checkbox-all">
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
                                    <span class="media-dropzone-label">Fotos hierher ziehen oder klicken</span>
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

                        <div class="row g-3 event-gallery media-gallery-grid">
                            <?php if (empty($gallery) && $isLoggedIn): ?>
                                <div class="col-12">
                                    <p class="text-muted p-2">
                                        <i class="ti ti-photo-off"></i>
                                        Noch keine Galeriebilder
                                    </p>
                                </div>
                            <?php endif; ?>

                            <?php foreach ($gallery as $media): ?>
                                <div class="col-12 col-md-6 col-lg-4 col-xl-3 gallery-item" data-media-id="<?= $media->id ?>">
                                    <?php if ($isLoggedIn): ?>
                                        <label class="gallery-item-checkbox">
                                            <input type="checkbox" class="gallery-checkbox"
                                                value="<?= $media->id ?>"
                                                data-caption="<?= htmlspecialchars($media->caption ?? '') ?>"
                                                data-credit="<?= htmlspecialchars($media->credit ?? '') ?>">
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
                </div>
            </div>
        <?php endif; ?>

        <!-- Back -->
        <div class="row mt-4">
            <div class="col-12">
                <a href="<?= $backUrl ?>" class="nav-icon-ux">
                    <i class="ti ti-arrow-left"></i> <?= $backLabel ?>
                </a>
            </div>
        </div>

    </div>
</section>