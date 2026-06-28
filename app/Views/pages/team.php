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
 *   $members  array  Team members from TeamModel with profileImg
 */
?>
<?php $sectionsMode = 'intro'; ?>
<?php require __DIR__ . '/../components/sections/render-sections.php'; ?>

<section class="segment light-segment">
    <div class="container">
        <!-- Message when empty -->
        <?php
        $visibleMembers = $isLoggedIn
            ? $members
            : array_filter($members, fn($m) => ($m->status ?? 'draft') === 'published');
        ?>
        <?php if (empty($visibleMembers)): ?>
            <p>Derzeit keine Teammitglieder verfügbar.</p>
        <?php else: ?>

            <div class="row g-4">
                <?php foreach ($members as $member): ?>
                    <?php
                    $mStatus  = $member->status ?? 'draft';
                    if (!$isLoggedIn && $mStatus !== 'published') continue;
                    $mIsDraft = $isLoggedIn && $mStatus === 'draft';
                    ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="/team/<?= htmlspecialchars($member->slug) ?>" class="team-card<?= $mIsDraft ? ' event-card--draft' : '' ?>">
                            <div class="img-placeholder portrait-img" style="position:relative;">
                                <?php if ($mIsDraft): ?>
                                    <span class="event-status-badge event-status-badge--draft">Entwurf</span>
                                <?php endif; ?>
                                <?php if (!empty($member->profileImg)): ?>
                                    <img src="<?= htmlspecialchars($member->profileImg->media_url) ?>"
                                        alt="<?= htmlspecialchars(TeamModel::displayName($member)) ?>">
                                <?php else: ?>
                                    <i class="ti ti-user"></i>
                                <?php endif; ?>
                            </div>
                            <div class="team-card__content">
                                <h3><?= htmlspecialchars(TeamModel::displayName($member)) ?></h3>
                                <?php if (!empty($member->role)): ?>
                                    <p class="team-card__role"><?= htmlspecialchars($member->role) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($member->profession)): ?>
                                    <small><?= htmlspecialchars($member->profession) ?></small>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php $sectionsMode = 'rest'; ?>
<?php require __DIR__ . '/../components/sections/render-sections.php'; ?>