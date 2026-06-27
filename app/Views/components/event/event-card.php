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
    <?php
    $cardStatus    = $event->status ?? 'published';
    $cardCancelled = !empty($event->cancelled_at);
    $cardClass     = '';
    $cardBadge     = '';
    if ($isLoggedIn) {
        if ($cardCancelled) {
            $cardClass = ' event-card--cancelled';
            $cardBadge = '<span class="event-status-badge event-status-badge--cancelled">Abgesagt</span>';
        } elseif ($cardStatus === 'draft') {
            $cardClass = ' event-card--draft';
            $cardBadge = '<span class="event-status-badge event-status-badge--draft">Entwurf</span>';
        }
    }
    ?>
    <a href="/veranstaltungen/<?= htmlspecialchars($event->slug) ?>"
        class="event-card<?= $cardClass ?>">

        <!-- Promo image -->
        <div class="img-placeholder event-card-img">
            <?= $cardBadge ?? '' ?>
            <?php if (!empty($event->promo)): ?>
                <img src="<?= htmlspecialchars($event->promo->media_url) ?>"
                    alt="<?= htmlspecialchars($event->title) ?>">
            <?php else: ?>
                <i class="ti ti-music"></i>
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