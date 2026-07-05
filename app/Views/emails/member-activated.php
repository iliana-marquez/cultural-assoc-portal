<!DOCTYPE html>
<html lang="de">

<body style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 2rem;">

    <h2>Ihre Mitgliedschaft ist jetzt aktiv!</h2>

    <p>Hallo <?= htmlspecialchars($first_name) ?>,</p>

    <p>wir freuen uns, Ihnen mitteilen zu können, dass Ihre Mitgliedschaft bei <strong><?= htmlspecialchars($orgName) ?></strong> aktiviert wurde.</p>

    <p>Ihre Mitgliedschaft ist gültig bis: <strong><?= htmlspecialchars($expires_at) ?></strong></p>

    <p>Wir freuen uns darauf, Sie bald bei einer unserer Veranstaltungen begrüßen zu dürfen.</p>

    <p style="margin: 1rem 0;">
        → Entdecken Sie unser aktuelles Programm:
        <a href="https://<?= $_SERVER['HTTP_HOST'] ?>/programm"
            style="color: #900000; text-decoration: underline; font-weight: 600;">
            <?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/programm
        </a>
    </p>

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