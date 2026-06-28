<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Georgia, serif;
            color: #222;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        .wrap {
            max-width: 560px;
            margin: 2rem auto;
            padding: 2rem;
            border-top: 3px solid #222;
        }

        h2 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #888;
            margin-bottom: 0.25rem;
        }

        .value {
            font-size: 0.95rem;
            margin-bottom: 1.25rem;
        }

        .message {
            white-space: pre-wrap;
            line-height: 1.6;
        }

        .footer {
            margin-top: 2rem;
            font-size: 0.8rem;
            color: #aaa;
            border-top: 1px solid #eee;
            padding-top: 1rem;
        }
    </style>
</head>

<body>
    <div class="wrap">
        <h2>Neue Nachricht über die Website</h2>
        <div class="label">Name</div>
        <div class="value"><?= htmlspecialchars($name) ?></div>
        <div class="label">E-Mail</div>
        <div class="value"><a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a></div>
        <div class="label">Nachricht</div>
        <div class="value message"><?= nl2br(htmlspecialchars($message)) ?></div>
        <div class="footer"><?= htmlspecialchars($orgName) ?> — Kontaktformular</div>
    </div>
</body>

</html>