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

            <?php
            // Legal representative — order_index 0, locked from dragging,
            // assigned only via org-edit's select, never through team-grid
            // drag-and-drop. May be absent if no published member holds it.
            $legalRepMember = null;
            $draggableMembers = [];
            foreach ($members as $member) {
                if ((int) ($member->order_index ?? 999) === 0) {
                    $legalRepMember = $member;
                } else {
                    $draggableMembers[] = $member;
                }
            }

            $renderCard = function ($member) use ($isLoggedIn) {
                $mStatus  = $member->status ?? 'draft';
                if (!$isLoggedIn && $mStatus !== 'published') return;
                $mIsDraft = $isLoggedIn && $mStatus === 'draft';
            ?>
                <a href="/team/<?= htmlspecialchars($member->slug) ?>" class="team-staff-card<?= $mIsDraft ? ' event-card--draft' : '' ?>">
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
            <?php
            };
            ?>

            <?php if ($legalRepMember): ?>
                <?php
                $lStatus  = $legalRepMember->status ?? 'draft';
                $showLegalRep = $isLoggedIn || $lStatus === 'published';
                ?>
                <?php if ($showLegalRep): ?>
                    <div class="row g-4 mb-4 team-legal-rep-row">
                        <div class="col-12 col-md-6 col-lg-4">
                            <?php $renderCard($legalRepMember); ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="team-staff-edit-row" data-save-url="/team/reorder">
                <?php if ($isLoggedIn): ?>
                    <div class="edit-row-header">
                        <label class="edit-row-label">Mitarbeiter:innen — Anordnung</label>
                        <div class="edit-row-actions">
                            <span class="entity-feedback"></span>
                            <button class="entity-edit-btn team-staff-pencil-btn"><i class="ti ti-pencil"></i></button>
                            <button class="entity-save-btn team-staff-save-btn"><i class="ti ti-check"></i></button>
                            <button class="entity-cancel-btn team-staff-cancel-btn"><i class="ti ti-x"></i></button>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="row g-4 team-staff-grid">
                    <?php foreach ($draggableMembers as $member): ?>
                        <?php
                        $mStatus = $member->status ?? 'draft';
                        if (!$isLoggedIn && $mStatus !== 'published') continue;
                        ?>
                        <div class="col-12 col-md-6 col-lg-4 team-staff-card" data-member-id="<?= $member->id ?>">
                            <?php $renderCard($member); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php $sectionsMode = 'rest'; ?>
<?php require __DIR__ . '/../components/sections/render-sections.php'; ?>