<?php

/**
 * components/entity-venues.php
 *
 * Reusable venue partial — display and edit mode.
 * Follows entity-urls.php edit-row pattern — CSS drives state via .editing.
 *
 * Required variables:
 *   $entityType  string
 *   $entityId    int
 *   $event       object  venue_id, venue_name, venue_street,
 *                        venue_postcode, venue_city, venue (full object)
 *   $isLoggedIn  bool
 */

$venue  = $event->venue ?? null;
$mapUrl = $venue->map_url     ?? null;
$webUrl = $venue->website_url ?? null;
?>

<div class="event-venues venue-edit-row"
    data-entity-type="<?= htmlspecialchars($entityType) ?>"
    data-entity-id="<?= (int) $entityId ?>"
    data-venue-id="<?= $event->venue_id ?? '' ?>">

    <?php if ($isLoggedIn): ?>
        <div class="edit-row-header">
            <label class="edit-row-label">Veranstaltungsort</label>
            <div class="edit-row-actions">
                <span class="entity-feedback"></span>
                <button class="entity-edit-btn venue-pencil-btn"><i class="ti ti-pencil"></i></button>
                <button class="entity-cancel-btn venue-cancel-btn"><i class="ti ti-x"></i></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Venue item -->
    <div class="venue-item p-2">
        <?php if (!empty($event->venue_name)): ?>
            <?php if ($isLoggedIn): ?>
                <button class="entity-edit-btn border-0" data-action="edit-venue"
                    data-venue-id="<?= $event->venue_id ?>"
                    data-venue-name="<?= htmlspecialchars($event->venue_name) ?>"
                    data-venue-street="<?= htmlspecialchars($event->venue_street ?? '') ?>"
                    data-venue-postcode="<?= htmlspecialchars($event->venue_postcode ?? '') ?>"
                    data-venue-city="<?= htmlspecialchars($event->venue_city ?? '') ?>"
                    data-venue-country="<?= htmlspecialchars($venue->country ?? '') ?>"
                    data-venue-map-url="<?= htmlspecialchars($mapUrl ?? '') ?>"
                    data-venue-website-url="<?= htmlspecialchars($webUrl ?? '') ?>">
                    <i class="ti ti-pencil"></i>
                </button>
                <button class="entity-remove-btn border-0" data-action="remove-venue">
                    <i class="ti ti-trash"></i>
                </button>
            <?php endif; ?>
            <div class="venue-item-content">
                <p class="mb-0">
                    <?php if ($webUrl): ?>
                        <a href="<?= htmlspecialchars($webUrl) ?>" target="_blank" rel="noopener noreferrer">
                            <strong><?= htmlspecialchars($event->venue_name) ?></strong>
                        </a>
                    <?php else: ?>
                        <strong><?= htmlspecialchars($event->venue_name) ?></strong>
                    <?php endif; ?>
                </p>
                <?php if (!empty($event->venue_street)): ?>
                    <small class="text-muted">
                        <?php if ($mapUrl): ?>
                            <a href="<?= htmlspecialchars($mapUrl) ?>" target="_blank" rel="noopener noreferrer">
                                <?= htmlspecialchars($event->venue_street) ?>,
                                <?= htmlspecialchars($event->venue_postcode) ?>
                                <?= htmlspecialchars($event->venue_city) ?>
                            </a>
                        <?php else: ?>
                            <?= htmlspecialchars($event->venue_street) ?>,
                            <?= htmlspecialchars($event->venue_postcode) ?>
                            <?= htmlspecialchars($event->venue_city) ?>
                        <?php endif; ?>
                    </small>
                <?php endif; ?>
            </div>
        <?php elseif ($isLoggedIn): ?>
            <p class="text-muted mb-0">— kein Ort —</p>
        <?php endif; ?>
    </div>

    <?php if ($isLoggedIn): ?>
        <!-- Change/add venue — visible only in edit mode (CSS) -->
        <div class="venue-change-wrap p-2">
            <button class="entity-edit-btn" data-action="open-venue-modal">
                <i class="ti ti-building-bank"></i>
                <?= !empty($event->venue_id) ? 'Ort ändern' : 'Ort hinzufügen' ?>
            </button>
        </div>
    <?php endif; ?>

</div>