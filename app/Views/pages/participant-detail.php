<?php

/**
 * participant-detail.php
 * Single participant detail page.
 */
?>

<section class="segment light-segment">
    <div class="container">
        <div class="row align-items-start g-5">

            <!-- Image -->
            <div class="col-12 col-md-4">
                <?php if (!empty($participant->media)): ?>
                    <div class="img-placeholder portrait-img">
                        <img src="<?= htmlspecialchars($participant->media[0]->media_url) ?>"
                            alt="<?= htmlspecialchars($participant->displayName) ?>">
                    </div>
                    <?php if (!empty($participant->image_credit)): ?>
                        <span class="image-credit small">
                            <i class="ti ti-camera"></i>
                            <?= htmlspecialchars($participant->image_credit) ?>
                        </span>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="img-placeholder portrait-img">
                        <i class="ti ti-user"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Content -->
            <div class="col-12 col-md-8">
                <div class="section-content">

                    <h1><?= htmlspecialchars($participant->displayName) ?></h1>

                    <?php if (!empty($participant->field)): ?>
                        <h2><?= htmlspecialchars($participant->field) ?></h2>
                    <?php endif; ?>

                    <?php if (!empty($participant->type) && $participant->type !== 'individual'): ?>
                        <small><?= htmlspecialchars(ucfirst($participant->type)) ?></small>
                    <?php endif; ?>

                    <?php if (!empty($participant->urls)): ?>
                        <nav class="nav-socials"
                            aria-label="<?= htmlspecialchars($participant->displayName) ?> Links">
                            <?php foreach ($participant->urls as $url): ?>
                                <a href="<?= htmlspecialchars($url->url) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    title="<?= htmlspecialchars($url->type_label) ?>">
                                    <i class="ti <?= htmlspecialchars($url->icon) ?>"></i>
                                </a>
                            <?php endforeach; ?>
                        </nav>
                    <?php endif; ?>

                    <?php if (!empty($participant->events)): ?>
                        <div class="participant-events">
                            <h3>Veranstaltungen</h3>
                            <ul class="participant-events-list">
                                <?php foreach ($participant->events as $event): ?>
                                    <li>
                                        <a href="/veranstaltungen/<?= htmlspecialchars($event->slug) ?>">
                                            <?= htmlspecialchars($event->title) ?>
                                            <?php if (!empty($event->date)): ?>
                                                <small><?= date('d.m.Y', strtotime($event->date)) ?></small>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
            <hr>
            <a href="/kuenstlerinnen" class="nav-icon-ux">
                <i class="ti ti-arrow-left"></i> Künstler:innen
            </a><br>
            <a href="/veranstaltungen" class="nav-icon-ux">
                <i class=" ti ti-arrow-left"></i> Veranstaltungen
            </a>

        </div>
    </div>
</section>