<?php

/**
 * login.php
 *
 * OTP login form — email input step.
 * Posts to /{admin_path} → AuthController::sendOtp()
 */
?>

<section class="light-segment segment container">

    <div class="col-6 align-self-center">
        <div class="contact-form-wrap">
            <h1>Editor login</h1>

            <?php if (!empty($error)): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="POST" action="/<?= htmlspecialchars($config['admin_path']) ?>">
                <div class="form-group">
                    <label for="email">E-Mail-Adresse</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-group"
                        required
                        autofocus
                        placeholder="ihre@email.com">
                    <button type="submit" class="btn-section">Code senden</button>
                </div>
            </form>
        </div>
    </div>
</section>