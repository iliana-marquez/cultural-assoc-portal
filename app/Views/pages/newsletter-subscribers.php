<?php

/**
 * newsletter-subscribers.php
 *
 * Editor-only view — confirmed subscriber list with CSV export.
 * Variables:
 *   $subscribers array
 *   $isLoggedIn  bool
 */
?>
<section class="segment light-segment">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1>Newsletter Abonnenten</h1>
            <a href="/newsletter/export" class="btn-section">
                <i class="ti ti-download"></i> CSV exportieren
            </a>
        </div>

        <?php if (empty($subscribers)): ?>
            <p class="text-muted">Noch keine bestätigten Abonnenten.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>E-Mail</th>
                        <th>Bestätigt am</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscribers as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s->email) ?></td>
                            <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($s->confirmed_at))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="text-muted small"><?= count($subscribers) ?> bestätigte Abonnenten</p>
        <?php endif; ?>
    </div>
</section>