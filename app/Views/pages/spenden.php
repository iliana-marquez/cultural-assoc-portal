<?php

/**
 * spenden.php
 *
 * Donation info page — /spenden
 * Free sections above via render-sections.php (editor fills context, motivation)
 * Hardcoded donation section below — org bank details, QR code, donation_note
 * All data from $org (BaseController) — managed via org settings.
 *
 * IBAN/BIC stored as-is — spaces stripped only for QR code generation.
 * QR code requires IBAN + account_holder minimum.
 *
 * Shareable URL — used for direct donation links in emails, social media, CTAs.
 */

$sectionsMode = 'intro';
require __DIR__ . '/../components/sections/render-sections.php';

$hasBank = !empty($org->iban) || !empty($org->account_holder);
$hasQr   = !empty($org->iban) && !empty($org->account_holder);

$qrIban    = str_replace(' ', '', $org->iban    ?? '');
$qrBic     = str_replace(' ', '', $org->bic     ?? '');
$qrHolder  = $org->account_holder               ?? '';
$qrPurpose = $org->donation_purpose             ?? '';
?>

<section class="segment light-segment" id="spenden-info">
    <div class="container">

        <?php if (!$hasBank): ?>

            <p class="text-muted">Die Bankverbindung für Spenden wird in Kürze veröffentlicht. Vielen Dank für Ihr Interesse!</p>

        <?php else: ?>

            <?php if ($hasQr): ?>
                <h3>Danke für Ihre Unterstützung! So einfach geht's:</h3>
                <p class="text-muted pb-1">Scannen Sie den QR-Code mit Ihrer Banking-App oder verwenden Sie die unten angeführten Bankdaten für Ihre Überweisung.</p>
            <?php else: ?>
                <h3>Danke für Ihre Unterstützung!</h3>
                <p class="text-muted pb-1">Verwenden Sie die unten angeführten Bankdaten für Ihre Überweisung.</p>
            <?php endif; ?>


            <div class="row g-4 align-items-start py-3">

                <?php if ($hasQr): ?>
                    <div class="col-auto">
                        <div id="spenden-qr-code"></div>
                        <small class="text-muted d-block text-center mt-1">Banking-App scannen</small>
                    </div>
                <?php endif; ?>

                <div class="col">
                    <div class="spenden-bank-details">

                        <?php if (!empty($org->account_holder)): ?>
                            <div class="spenden-field">
                                <small class="spenden-field-label">Kontoinhaber</small>
                                <div class="spenden-field-value">
                                    <span id="spenden-account-holder"><?= htmlspecialchars($org->account_holder) ?></span>
                                    <button class="spenden-copy-btn" data-target="spenden-account-holder" title="Kopieren">
                                        <i class="ti ti-copy"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($org->iban)): ?>
                            <div class="spenden-field">
                                <small class="spenden-field-label">IBAN</small>
                                <div class="spenden-field-value">
                                    <span id="spenden-iban"><?= htmlspecialchars($org->iban) ?></span>
                                    <button class="spenden-copy-btn" data-target="spenden-iban" title="Kopieren">
                                        <i class="ti ti-copy"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($org->bic)): ?>
                            <div class="spenden-field">
                                <small class="spenden-field-label">BIC</small>
                                <div class="spenden-field-value">
                                    <span id="spenden-bic"><?= htmlspecialchars($org->bic) ?></span>
                                    <button class="spenden-copy-btn" data-target="spenden-bic" title="Kopieren">
                                        <i class="ti ti-copy"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($org->donation_purpose)): ?>
                            <div class="spenden-field">
                                <small class="spenden-field-label">Verwendungszweck</small>
                                <div class="spenden-field-value">
                                    <span id="spenden-purpose"><?= htmlspecialchars($org->donation_purpose) ?></span>
                                    <button class="spenden-copy-btn" data-target="spenden-purpose" title="Kopieren">
                                        <i class="ti ti-copy"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

            </div>

            <?php if (!empty($org->donation_note)): ?>
                <p class="small text-muted mb-4"><strong>*Hinweis:</strong><br><?= htmlspecialchars($org->donation_note) ?></p>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</section>

<?php if ($hasQr): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new QRCode(document.getElementById('spenden-qr-code'), {
                text: 'BCD\n002\n1\nSCT\n<?= $qrBic ?>\n<?= addslashes($qrHolder) ?>\n<?= $qrIban ?>\nEUR\n\n\n<?= addslashes($qrPurpose) ?>',
                width: 180,
                height: 180,
                correctLevel: QRCode.CorrectLevel.M
            });
        });
    </script>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.spenden-copy-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var target = document.getElementById(btn.dataset.target);
                if (!target) return;
                navigator.clipboard.writeText(target.textContent.trim()).then(function() {
                    var icon = btn.querySelector('i');
                    icon.className = 'ti ti-check';
                    setTimeout(function() {
                        icon.className = 'ti ti-copy';
                    }, 2000);
                });
            });
        });
    });
</script>

<?php
$sectionsMode = 'rest';
require __DIR__ . '/../components/sections/render-sections.php';
?>