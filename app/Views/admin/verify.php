<?php

/**
 * verify.php
 *
 * OTP verify form — 6-digit code input step.
 * Posts to /{admin_path}/verify → AuthController::verifyOtp()
 */
?>

<section class="auth light-segment segment container">

    <div class="col-6 align-self-center">
        <div class="contact-form-wrap">
            <h1>Code eingeben</h1>

            <p>
                Ein 6-stelliger Code wurde gesendet.
                Bitte prüfen Sie Ihr Postfach.
            </p>

            <?php if (!empty($error)): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="POST" action="/<?= htmlspecialchars($config['admin_path']) ?>/verify">
                <div class="form-group">
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
                        style="width: auto;"
                        autocomplete="one-time-code">
                    <button class="btn-section" type="submit">Anmelden</button>
                </div>
            </form>
            <div class="margin-top">
                <a href="/<?= htmlspecialchars($config['admin_path']) ?>">
                    Andere E-Mail verwenden
                </a>
            </div>

        </div>
    </div>
</section>