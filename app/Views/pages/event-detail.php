<?php

/**
 * event-detail.php
 *
 * Single event detail page.
 * Row 1: promo image | event details
 * Row 2: review | video (if any)
 * Row 3: gallery (zoomable)
 *
 * Variables:
 *   $event object  Event with participants, media, status
 */

// Split media by stage and resource_type
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
?>

<section class="segment light-segment">
    <div class="container">

        <!-- Row 1: Promo image | Event details -->
        <div class="row g-5 align-items-start">

            <!-- Promo image / carousel -->
            <div class="col-12 col-md-6">
                <?php if (!empty($promoImages)): ?>

                    <?php if (count($promoImages) === 1): ?>
                        <!-- Single promo image -->
                        <div class="section-image-wrap">
                            <img src="<?= htmlspecialchars($promoImages[0]->media_url) ?>"
                                alt="<?= htmlspecialchars($event->title) ?>"
                                class="section-image zoomable">
                        </div>
                        <?php if (!empty($promoImages[0]->caption)): ?>
                            <small class="image-credit">
                                <i class="ti ti-camera"></i>
                                <?= htmlspecialchars($promoImages[0]->caption) ?>
                            </small>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- Multiple promo images — Bootstrap carousel -->
                        <div id="eventPromo" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach ($promoImages as $i => $media): ?>
                                    <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                                        <img src="<?= htmlspecialchars($media->media_url) ?>"
                                            alt="<?= htmlspecialchars($media->caption ?? $event->title) ?>"
                                            class="d-block w-100 section-image zoomable">
                                        <?php if (!empty($media->caption)): ?>
                                            <div class="carousel-caption d-none d-md-block">
                                                <small><?= htmlspecialchars($media->caption) ?></small>
                                            </div>
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
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="section-image-placeholder">
                        <i class="ti ti-music"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Event details -->
            <div class="col-12 col-md-6">
                <div class="section-content">

                    <?php if (!empty($event->category_label)): ?>
                        <small class="event-card__category">
                            <?= htmlspecialchars($event->category_label) ?>
                        </small>
                    <?php endif; ?>

                    <h1><?= htmlspecialchars($event->title) ?></h1>

                    <?php if (!empty($event->subtitle)): ?>
                        <h2><?= htmlspecialchars($event->subtitle) ?></h2>
                    <?php endif; ?>

                    <!-- Meta -->
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

                    <!-- Description -->
                    <?php if (!empty($event->description)): ?>
                        <p><?= nl2br(htmlspecialchars($event->description)) ?></p>
                    <?php endif; ?>

                    <!-- Participants -->
                    <?php if (!empty($event->participants)): ?>
                        <div class="event-participants">
                            <h3>Mitwirkende</h3>
                            <div class="participant-chips">
                                <?php foreach ($event->participants as $participant): ?>
                                    <a href="/kuenstlerinnen/<?= htmlspecialchars($participant->slug) ?>"
                                        class="participant-chip">
                                        <?= htmlspecialchars($participant->displayName) ?>
                                        <?php if (!empty($participant->type) && $participant->type !== 'individual'): ?>
                                            <small><?= htmlspecialchars($participant->type) ?></small>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Admission -->
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
                                    . ' Eintritt frei &middot; Spenden willkommen ab ' . $admissionAmount
                                    . '</span>'
                                ),
                                'reserve' => print(
                                    '<div class="admission-action">'
                                    . '<span class="event-admission-label">'
                                    . '<i class="ti ti-ticket"></i>'
                                    . ' Anmeldung erforderlich'
                                    . ($admissionAmount ? ' &middot; ' . $admissionAmount : '')
                                    . '</span>'
                                    . '<a href="' . $admissionUrl . '" class="btn-section">'
                                    . '<i class="ti ti-ticket"></i> Jetzt anmelden'
                                    . '</a>'
                                    . '</div>'
                                ),
                                'ticket' => print(
                                    '<div class="admission-action">'
                                    . '<span class="event-admission-label">'
                                    . '<i class="ti ti-ticket"></i>'
                                    . ' Tickets'
                                    . ($admissionAmount ? ': ' . $admissionAmount : '')
                                    . '</span>'
                                    . '<a href="' . $admissionUrl . '" target="_blank" class="btn-section">'
                                    . '<i class="ti ti-ticket"></i> Tickets kaufen'
                                    . '</a>'
                                    . '</div>'
                                ),
                                default => null,
                            }; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>

        <!-- Row 2: Review | Video -->
        <?php if (!empty($event->review) || !empty($videos)): ?>
            <div class="row g-5 align-items-start event-review-row">

                <!-- Review -->
                <?php if (!empty($event->review)): ?>
                    <div class="col-12 <?= !empty($videos) ? 'col-md-8' : 'col-12' ?>">
                        <div class="event-review">
                            <h3>Rückblick</h3>
                            <p><?= nl2br(htmlspecialchars($event->review)) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Videos -->
                <?php if (!empty($videos)): ?>
                    <div class="col-12 col-md-4">
                        <?php foreach ($videos as $video): ?>
                            <div class="event-media-item">
                                <video src="<?= htmlspecialchars($video->media_url) ?>"
                                    class="section-image"
                                    controls>
                                </video>
                                <?php if (!empty($video->caption)): ?>
                                    <small><?= htmlspecialchars($video->caption) ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>

        <!-- Row 3: Gallery -->
        <?php if (!empty($gallery)): ?>
            <div class="row g-3 event-gallery">
                <?php foreach ($gallery as $media): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <img src="<?= htmlspecialchars($media->media_url) ?>"
                            alt="<?= htmlspecialchars($media->caption ?? $event->title) ?>"
                            class="section-image zoomable">
                        <?php if (!empty($media->caption)): ?>
                            <small><?= htmlspecialchars($media->caption) ?></small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Back link -->
        <div class="row mt-4">
            <div class="col-12">
                <a href="/veranstaltungen" class="nav-icon-ux">
                    <i class="ti ti-arrow-left"></i> Veranstaltungen
                </a>
            </div>
        </div>

    </div>
</section>