<!DOCTYPE html>
<html lang="de">

<body style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 2rem;">

    <h2>Ihre Mitgliedschaft wurde verlängert!</h2>

    <p>Hallo <?= htmlspecialchars($first_name) ?>,</p>

    <p>vielen Dank für Ihre Treue! Ihre Mitgliedschaft bei <strong><?= htmlspecialchars($orgName) ?></strong> wurde erfolgreich verlängert.</p>

    <p>Ihre Mitgliedschaft ist gültig bis: <strong><?= htmlspecialchars($expires_at) ?></strong></p>

    <p>Wir freuen uns darauf, Sie weiterhin bei unseren Veranstaltungen begrüßen zu dürfen.</p>

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

</body>

</html>