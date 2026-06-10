<?php

/**
 * verify.php
 *
 * OTP verify form — 6-digit code input step.
 * Posts to /{admin_path}/verify → AuthController::verifyOtp()
 */
?>

<section class="auth light-segment segment">
    <h1>Code eingeben</h1>

    <p>
        Ein 6-stelliger Code wurde gesendet.
        Bitte prüfen Sie Ihr Postfach.
    </p>

    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="/<?= htmlspecialchars($config['admin_path']) ?>/verify">
        <label for="code">Einmalcode</label>
        <input
            type="text"
            id="code"
            name="code"
            required
            autofocus
            maxlength="6"
            placeholder="000000"
            inputmode="numeric"
            autocomplete="one-time-code">
        <button type="submit">Anmelden</button>
    </form>

    <a href="/<?= htmlspecialchars($config['admin_path']) ?>">
        Andere E-Mail verwenden
    </a>
</section>