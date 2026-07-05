<!DOCTYPE html>
<html lang="de">

<body style="font-family: Arial, sans-serif; color: #333; max-width: 700px; margin: 0 auto; padding: 2rem;">

    <h2>Hallo <?= htmlspecialchars($first_name) ?>,</h2>

    <p>
        wir freuen uns, dass Sie Mitglied bei <?= htmlspecialchars($orgName) ?> werden.
    </p>

    <p>
        Um Ihre Mitgliedschaft abzuschließen, überweisen Sie bitte den Mitgliedsbeitrag von
        <strong><?= htmlspecialchars($org->membership_fee) ?></strong>
        auf folgendes Konto:
    </p>
    <hr>

    <!-- Bankverbidnung -->
    <div>
        <?php if (!empty($account_holder)): ?>
            <p style="margin-bottom: 1rem;">
                <strong style="font-size: 12px;">KONTOINHABER</strong><br>
                <?= htmlspecialchars($account_holder) ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($iban)): ?>
            <p style="margin: 1rem 0;">
                <strong style="font-size: 12px;">IBAN</strong><br>
                <?= htmlspecialchars($iban) ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($bic)): ?>
            <p style="margin: 1rem 0;">
                <strong style="font-size: 12px;">BIC</strong><br>
                <?= htmlspecialchars($bic) ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($payment_reference)): ?>
            <p style="margin-bottom: 0;">
                <strong style="font-size: 12px;">
                    VERWENDUNGSZWECK<br>
                    <span style="font-size: 10px;">bitte unverändert für eindeutige Zuordnung:</span>
                </strong>
            </p>
            <p><?= htmlspecialchars($payment_reference) ?></p>
        <?php endif; ?>

    </div>
    <hr>

    <p>Nach Eingang Ihrer Zahlung aktivieren wir Ihre Mitgliedschaft. Anschließend erhalten Sie eine Bestätigung per E-Mail. Sie müssen nichts weiter unternehmen.</p>

    <h4>Als Mitglied profitieren Sie unter anderem von:</h4>

    <ul>
        <li>Aktuelle Informationen zu unseren Aktivitäten</li>
        <li>Sitzplatzreservierung bei Voranmeldung</li>
        <li>Meet &amp; Greet mit den Künstler:innen</li>
        <li>Autogrammen und persönlichen Widmungen</li>
        <li>Dem jährlichen Mitgliedertreffen mit künstlerischen Darbietungen</li>
        <li>Einer kleinen Aufmerksamkeit vor jedem Besuch einer Vorstellung</li>
    </ul>

    <p>Vielen Dank für Ihr Vertrauen und Ihre Unterstützung, die unser aktuelles Programm ermöglicht.</p>

    <p style="margin: 1rem 0;">
        →
        <a href="https://<?= $_SERVER['HTTP_HOST'] ?>/programm"
            style="color: #900000; text-decoration: underline; font-weight: 600;">Entdecken Sie unser aktuelles Programm
        </a>
    </p>

    <p>Wir freuen uns darauf, Sie bald bei einer unserer Veranstaltungen persönlich begrüßen zu dürfen.</p>

    <p>Herzliche Grüße,<br>
        <strong><?= htmlspecialchars($orgName) ?></strong>
    </p>

    <?php if (!empty($org->inline_logo_url)): ?>
        <div style="margin-top: 0.5rem;">
            <img src="<?= htmlspecialchars($org->inline_logo_url) ?>"
                alt="<?= htmlspecialchars($org->name ?? '') ?>"
                style="max-height: 40px;">
        </div>
    <?php endif; ?>

    <?php if (!empty($org->urls)): ?>
        <hr>
        <p>
            <?php foreach ($org->urls as $url): ?>
                <a href="<?= htmlspecialchars($url->url) ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    style="color: #900000; text-decoration: underline; font-weight: 600; margin-right: 1rem;">
                    <?= strtoupper(htmlspecialchars($url->type_label)) ?>
                </a>
            <?php endforeach; ?>
        </p>
    <?php endif; ?>

</body>

</html>