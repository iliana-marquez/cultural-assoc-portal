<?php

/**
 * team-detail.php
 *
 * Team member detail page.
 * Image left, content right — 50/50 layout.
 *
 * Variables:
 *   $member object  Team member from TeamModel
 *   $urls   array   Member URLs from UrlModel
 */
?>

<section class="segment light-segment">
    <div class="container">
        <div class="row align-items-start g-5">

            <!-- Image -->
            <div class="col-12 col-md-5">
                <?php if (!empty($member->image)): ?>
                    <div class="team-detail-image">
                        <img src="<?= htmlspecialchars($member->image) ?>"
                            alt="<?= htmlspecialchars(TeamModel::displayName($member)) ?>"
                            class="team-detail-image">
                    </div>
                    <?php if (!empty($member->image_credits)): ?>
                        <span class="image-credit small">
                            <i class="ti ti-camera"></i>
                            <?= htmlspecialchars($member->image_credits) ?>
                        </span>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="section-image-placeholder">
                        <i class="ti ti-user"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Content -->
            <div class="col-12 col-md-7">
                <div class="section-content">

                    <h1><?= htmlspecialchars(TeamModel::displayName($member)) ?></h1>

                    <?php if (!empty($member->role)): ?>
                        <h2><?= htmlspecialchars($member->role) ?></h2>
                    <?php endif; ?>

                    <?php if (!empty($member->profession)): ?>
                        <p><strong><?= htmlspecialchars($member->profession) ?></strong></p>
                    <?php endif; ?>

                    <?php if (!empty($member->motto)): ?>
                        <blockquote>
                            <?= htmlspecialchars($member->motto) ?>
                        </blockquote>
                    <?php endif; ?>

                    <?php if (!empty($member->biography)): ?>
                        <p><?= nl2br(htmlspecialchars($member->biography)) ?></p>
                    <?php endif; ?>

                    <!-- URLs -->
                    <?php if (!empty($urls)): ?>
                        <nav class="nav-socials" aria-label="<?= htmlspecialchars(TeamModel::displayName($member)) ?> Links">
                            <?php foreach ($urls as $url): ?>
                                <a href="<?= htmlspecialchars($url->url) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    title="<?= htmlspecialchars($url->type_label) ?>">
                                    <i class="ti <?= htmlspecialchars($url->icon) ?>"></i>
                                </a>
                            <?php endforeach; ?>
                        </nav>
                    <?php endif; ?>

                </div>
            </div>
            <hr>
            <a href="/team" class="d-flex align-items-end">
                <i class="ti ti-arrow-left align-items-"></i> Team
            </a>

        </div>
    </div>
</section>