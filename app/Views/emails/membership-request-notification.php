<!DOCTYPE html>
<html lang="de">

<body style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 2rem;">

    <h2>Neue Mitgliedschaftsanfrage</h2>
    <p>Folgende Person möchte Mitglied bei <strong><?= htmlspecialchars($orgName) ?></strong> werden:</p>

    <table style="width:100%; border-collapse: collapse; margin-top: 1rem;">
        <tr>
            <td style="padding: 0.5rem; font-weight: bold; width: 160px;">Vorname</td>
            <td style="padding: 0.5rem;"><?= htmlspecialchars($first_name) ?></td>
        </tr>
        <tr>
            <td style="padding: 0.5rem; font-weight: bold;">Nachname</td>
            <td style="padding: 0.5rem;"><?= htmlspecialchars($last_name) ?></td>
        </tr>
        <tr>
            <td style="padding: 0.5rem; font-weight: bold;">E-Mail</td>
            <td style="padding: 0.5rem;">
                <a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a>
            </td>
        </tr>
        <?php if (!empty($street)): ?>
            <tr>
                <td style="padding: 0.5rem; font-weight: bold;">Straße</td>
                <td style="padding: 0.5rem;"><?= htmlspecialchars($street) ?></td>
            </tr>
        <?php endif; ?>
        <?php if (!empty($plz) || !empty($city)): ?>
            <tr>
                <td style="padding: 0.5rem; font-weight: bold;">PLZ / Ort</td>
                <td style="padding: 0.5rem;"><?= htmlspecialchars($plz) ?> <?= htmlspecialchars($city) ?></td>
            </tr>
        <?php endif; ?>
        <?php if (!empty($phone)): ?>
            <tr>
                <td style="padding: 0.5rem; font-weight: bold;">Telefon</td>
                <td style="padding: 0.5rem;"><?= htmlspecialchars($phone) ?></td>
            </tr>
        <?php endif; ?>
        <?php if (!empty($birth_date)): ?>
            <tr>
                <td style="padding: 0.5rem; font-weight: bold;">Geburtstag</td>
                <td style="padding: 0.5rem;"><?= htmlspecialchars($birth_date) ?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <td style="padding: 0.5rem; font-weight: bold;">Newsletter</td>
            <td style="padding: 0.5rem;"><?= $newsletter ? 'Ja' : 'Nein' ?></td>
        </tr>
        <tr>
            <td style="padding: 0.5rem; font-weight: bold;">Verwendungszweck</td>
            <td style="padding: 0.5rem;"><strong><?= htmlspecialchars($payment_reference) ?></strong></td>
        </tr>
    </table>

    <hr style="margin: 2rem 0; border: none; border-top: 1px solid #eee;">
    <p style="font-size: 0.85rem; color: #666;">
        Diese Nachricht wurde automatisch von der Website gesendet.
    </p>

</body>

</html>