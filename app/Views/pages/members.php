<?php

/**
 * members.php
 *
 * Member management view — editor only.
 * Tabbed: Ausstehend / Aktiv / Abgelaufen
 * Each row: name · email · expiry · action buttons only.
 * No detail view — volunteer workflow is activate/renew/delete.
 */
?>

<section class="segment light-segment">
    <div class="container">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1>Mitglieder</h1>
                <p class="opacity-50">Mitgliedschaftsverwaltung</p>
            </div>
            <a href="/members/export" class="btn-section">
                <i class="ti ti-download"></i> CSV Export
            </a>
        </div>

        <!-- Tabs -->
        <div class="members-tabs">
            <button class="members-tab active" data-tab="pending">
                Ausstehend
                <?php if ($counts['pending'] > 0): ?>
                    <span class="members-tab-count"><?= $counts['pending'] ?></span>
                <?php endif; ?>
            </button>
            <button class="members-tab" data-tab="active">
                Aktiv
                <?php if ($counts['active'] > 0): ?>
                    <span class="members-tab-count"><?= $counts['active'] ?></span>
                <?php endif; ?>
            </button>
            <button class="members-tab" data-tab="expired">
                Abgelaufen
                <?php if ($counts['expired'] > 0): ?>
                    <span class="members-tab-count members-tab-count--alert"><?= $counts['expired'] ?></span>
                <?php endif; ?>
            </button>
        </div>

        <!-- Pending -->
        <div class="members-panel" id="tab-pending">
            <?php if (empty($pending)): ?>
                <p class="text-muted p-3">Keine ausstehenden Anfragen.</p>
            <?php else: ?>
                <div class="members-list">
                    <?php foreach ($pending as $member): ?>
                        <div class="member-row" data-member-id="<?= $member->id ?>">
                            <div class="member-row-info">
                                <span class="member-name"><?= htmlspecialchars($member->first_name . ' ' . $member->last_name) ?></span>
                                <span class="member-email"><?= htmlspecialchars($member->email) ?></span>
                                <span class="member-date">Angemeldet: <?= date('d.m.Y', strtotime($member->created_at)) ?></span>
                            </div>
                            <div class="member-row-actions">
                                <button class="btn-section member-activate-btn" data-id="<?= $member->id ?>">
                                    <i class="ti ti-check"></i> Aktivieren
                                </button>
                                <button class="entity-remove-btn member-delete-btn" data-id="<?= $member->id ?>">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Active -->
        <div class="members-panel" id="tab-active" style="display:none;">
            <?php if (empty($active)): ?>
                <p class="text-muted p-3">Keine aktiven Mitglieder.</p>
            <?php else: ?>
                <div class="members-list">
                    <?php foreach ($active as $member): ?>
                        <div class="member-row" data-member-id="<?= $member->id ?>">
                            <div class="member-row-info">
                                <span class="member-name"><?= htmlspecialchars($member->first_name . ' ' . $member->last_name) ?></span>
                                <span class="member-email"><?= htmlspecialchars($member->email) ?></span>
                                <span class="member-date">Aktiv bis: <?= date('d.m.Y', strtotime($member->expires_at)) ?></span>
                            </div>
                            <div class="member-row-actions">
                                <button class="btn-section member-renew-btn" data-id="<?= $member->id ?>">
                                    <i class="ti ti-refresh"></i> Verlängern
                                </button>
                                <button class="entity-remove-btn member-delete-btn" data-id="<?= $member->id ?>">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Expired -->
        <div class="members-panel" id="tab-expired" style="display:none;">
            <?php if (empty($expired)): ?>
                <p class="text-muted p-3">Keine abgelaufenen Mitgliedschaften.</p>
            <?php else: ?>
                <div class="members-list">
                    <?php foreach ($expired as $member): ?>
                        <div class="member-row" data-member-id="<?= $member->id ?>">
                            <div class="member-row-info">
                                <span class="member-name"><?= htmlspecialchars($member->first_name . ' ' . $member->last_name) ?></span>
                                <span class="member-email"><?= htmlspecialchars($member->email) ?></span>
                                <span class="member-date member-date--expired">Abgelaufen: <?= date('d.m.Y', strtotime($member->expires_at)) ?></span>
                            </div>
                            <div class="member-row-actions">
                                <button class="btn-section member-renew-btn" data-id="<?= $member->id ?>">
                                    <i class="ti ti-refresh"></i> Verlängern
                                </button>
                                <button class="entity-remove-btn member-delete-btn" data-id="<?= $member->id ?>">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>