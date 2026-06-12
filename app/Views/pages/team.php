<?php

/**
 * team.php
 *
 * Team listing page.
 * Free intro sections from pages table.
 * Team member cards — full card links to detail page.
 *
 * Variables:
 *   $sections array  Free sections from PagesModel
 *   $members  array  Team members from TeamModel
 */
?>

<?php if (!empty($sections)): ?>
    <?php require __DIR__ . '/../components/sections/render-sections.php'; ?>
<?php endif; ?>

<section class="segment light-segment">
    <div class="container">

        <?php if (empty($members)): ?>
            <p>Inhalt folgt in Kürze.</p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($members as $member): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="/team/<?= htmlspecialchars($member->slug) ?>"
                            class="team-card">

                            <div class="team-card__image">
                                <?php if (!empty($member->image)): ?>
                                    <img src="<?= htmlspecialchars($member->image) ?>"
                                        alt="<?= htmlspecialchars(TeamModel::displayName($member)) ?>"
                                        class="section-image">
                                <?php else: ?>
                                    <div class="section-image-placeholder">
                                        <i class="ti ti-user"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="team-card__content">
                                <h3><?= htmlspecialchars(TeamModel::displayName($member)) ?></h3>
                                <?php if (!empty($member->role)): ?>
                                    <p class="team-card__role">
                                        <?= htmlspecialchars($member->role) ?>
                                    </p>
                                <?php endif; ?>

                            </div>

                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>