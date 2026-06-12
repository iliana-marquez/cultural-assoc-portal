<?php

/**
 * event-detail.php
 *
 * Single event detail page.
 *
 * Variables:
 *   $event object  Event with participants, media, status
 */
?>

<section class="segment light-segment">
    <div class="container">
        <div class="row g-5">



            <!-- Media sidebar -->
            <div class="col-12 col-md-4">
                <?php if (!empty($event->media)): ?>
                    <?php foreach ($event->media as $media): ?>
                        <div class="event-media-item">
                            <img src="<?= htmlspecialchars($media->media_url) ?>"
                                alt="<?= htmlspecialchars($media->caption ?? $event->title) ?>"
                                class="section-image">
                            <?php if (!empty($media->caption)): ?>
                                <small><?= htmlspecialchars($media->caption) ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Main content -->
            <div class="col-12 col-md-8">
                <div class="section-content">

                    <!-- Category -->
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

                    <!-- Review (past events) -->
                    <?php if (!empty($event->review)): ?>
                        <div class="event-review">
                            <h3>Rückblick</h3>
                            <p><?= nl2br(htmlspecialchars($event->review)) ?></p>
                        </div>
                    <?php endif; ?>



                </div>
            </div>
            <hr>
            <a href="/veranstaltungen" class="d-flex align-items-end">
                <i class="ti ti-arrow-left"></i> Veranstaltungen
            </a>

        </div>
    </div>
</section>