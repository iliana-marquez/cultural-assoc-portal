<?php
// Variables available: $code, $orgName, $userName, $expiryMin
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body style="margin: 0; padding: 0; background-color: #f5f5f5; font-family: sans-serif;">
    <div style="max-width: 480px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden;">

        <div style="padding: 32px 40px; border-bottom: 1px solid #eeeeee;">
            <p style="margin: 0; font-size: 14px; color: #999999; text-transform: uppercase; letter-spacing: 0.08em;">
                <?= htmlspecialchars($orgName) ?>
            </p>
        </div>

        <div style="padding: 40px;">
            <p style="margin: 0 0 8px; font-size: 16px; color: #333333;">
                Hallo <?= htmlspecialchars($userName) ?>,
            </p>
            <p style="margin: 0 0 32px; font-size: 15px; color: #666666; line-height: 1.6;">
                Ihr Anmeldecode für den Website-Editor von <strong><?= htmlspecialchars($orgName) ?></strong>:
            </p>

            <div style="text-align: center; margin: 0 0 32px;">
                <span style="display: inline-block; font-size: 38px; font-weight: 700; letter-spacing: 12px; color: #111111; padding: 16px 24px; background: #f5f5f5; border-radius: 6px;">
                    <?= htmlspecialchars($code) ?>
                </span>
            </div>

            <p style="margin: 0 0 8px; font-size: 13px; color: #999999; text-align: center;">
                Dieser Code ist <strong><?= $expiryMin ?> Minuten</strong> gültig.
            </p>
            <p style="margin: 0; font-size: 13px; color: #999999; text-align: center;">
                Falls Sie diese Anfrage nicht gestellt haben, können Sie diese E-Mail ignorieren.
            </p>
        </div>

        <div style="padding: 24px 40px; background: #f9f9f9; border-top: 1px solid #eeeeee;">
            <p style="margin: 0; font-size: 12px; color: #bbbbbb; text-align: center;">
                <?= htmlspecialchars($orgName) ?> &mdash; Website Editor
            </p>
        </div>

    </div>
</body>

</html>