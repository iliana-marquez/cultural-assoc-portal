<?php

/**
 * event-card.php
 *
 * Reusable event card component.
 * Used in events listing and archive listing.
 *
 * Variables (from parent loop):
 *   $event object  Event with slug, promo, category_label, venue_name
 */
?>

<div class="col-12 col-md-6 col-lg-4">
    <a href="/veranstaltungen/<?= htmlspecialchars($event->slug) ?>"
        class="event-card">

        <!-- Promo image -->
        <div class="event-card__image">
            <?php if (!empty($event->promo)): ?>
                <img src="<?= htmlspecialchars($event->promo->media_url) ?>"
                    alt="<?= htmlspecialchars($event->title) ?>"
                    class="section-image">
            <?php else: ?>
                <div class="section-image-placeholder">
                    <i class="ti ti-music"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Event info -->
        <div class="event-card__content">

            <?php if (!empty($event->category_label)): ?>
                <small class="event-card__category">
                    <?= htmlspecialchars($event->category_label) ?>
                </small>
            <?php endif; ?>

            <h3><?= htmlspecialchars($event->title) ?></h3>

            <?php if (!empty($event->subtitle)): ?>
                <p class="event-card__subtitle">
                    <?= htmlspecialchars($event->subtitle) ?>
                </p>
            <?php endif; ?>

            <div class="event-card__meta">
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
                    </span>
                <?php endif; ?>
            </div>

        </div>

    </a>
</div>