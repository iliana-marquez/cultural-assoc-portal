<?php

/**
 * login.php
 *
 * OTP login form — email input step.
 * Posts to /{admin_path} → AuthController::sendOtp()
 */
?>

<section class="auth light-segment segment">
    <h1>Editor Login</h1>

    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="/<?= htmlspecialchars($config['admin_path']) ?>">
        <label for="email">E-Mail-Adresse</label>
        <input
            type="email"
            id="email"
            name="email"
            required
            autofocus
            placeholder="ihre@email.com">
        <button type="submit">Code senden</button>
    </form>
</section>