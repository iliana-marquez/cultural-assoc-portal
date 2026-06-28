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
            margin-bottom: 1rem;
        }

        p {
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .btn {
            display: inline-block;
            background: #222;
            color: #fff;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            margin: 1rem 0;
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
        <h2>Newsletter bestätigen</h2>
        <p>Vielen Dank für Ihr Interesse am Newsletter von <?= htmlspecialchars($orgName) ?>.</p>
        <p>Bitte bestätigen Sie Ihre Anmeldung mit einem Klick auf den folgenden Link:</p>
        <a href="<?= htmlspecialchars($confirmUrl) ?>" class="btn">Anmeldung bestätigen</a>
        <p>Der Link ist 24 Stunden gültig. Falls Sie sich nicht angemeldet haben, können Sie diese E-Mail ignorieren.</p>
        <div class="footer"><?= htmlspecialchars($orgName) ?> — Newsletter</div>
    </div>
</body>

</html>