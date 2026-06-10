<?php

/**
 * contact.php
 *
 * Contact page.
 * Free intro sections from pages table.
 * Contact info from organisation_info ($org from BaseController).
 * Social URLs from urls table ($urls from ContactController).
 * Static contact form — functionality in feat/contact-form.
 */
?>

<?php if (!empty($sections)): ?>
    <?php require __DIR__ . '/../components/sections/render-sections.php'; ?>
<?php endif; ?>

<section class="segment light-segment">
    <div class="container">
        <div class="row g-5">

            <!-- ── Contact info ───────────────────────────────── -->
            <div class="col-12 col-md-5">

                <!-- Anfragen & Feedback -->
                <div class="contact-block">
                    <h2>Anfragen &amp; Feedback</h2>
                    <?php if (!empty($org->email)): ?>
                        <a href="mailto:<?= htmlspecialchars($org->email) ?>" class="contact-link">
                            <i class="ti ti-mail"></i>
                            <?= htmlspecialchars($org->email) ?>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Veranstalter -->
                <div class="contact-block">
                    <h2>Veranstalter</h2>
                    <?php if (!empty($org->name)): ?>
                        <p><?= htmlspecialchars($org->name) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($org->street)): ?>
                        <p class="contact-address">
                            <i class="ti ti-map-pin"></i>
                            <?= htmlspecialchars($org->street) ?>,<br>
                            <?= htmlspecialchars($org->postcode) ?>
                            <?= htmlspecialchars($org->city) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Social links -->
                <?php if (!empty($urls)): ?>
                    <div class="contact-block">
                        <nav class="nav-socials" aria-label="Social Media">
                            <?php foreach ($urls as $url): ?>
                                <a href="<?= htmlspecialchars($url->url) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    title="<?= htmlspecialchars($url->label) ?>">
                                    <i class="ti <?= htmlspecialchars($url->icon) ?>"></i>
                                </a>
                            <?php endforeach; ?>
                        </nav>
                    </div>
                <?php endif; ?>

                <!-- Registration number -->
                <?php if (!empty($org->registration_number)): ?>
                    <div class="contact-block">
                        <p>
                            ZVR-Zahl: <?= htmlspecialchars($org->registration_number) ?>
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Website credit -->
                <div class="contact-block contact-credit">
                    <h2>Websitegestaltung</h2>
                    <p>Christina Mayer <small>(2025)</small></p>
                    <a href="https://ilianamarquez.com" target="_blank" rel="noopener noreferrer">
                        Iliana Márquez <small>(2026)</small>
                    </a>

                </div>

            </div>

            <!-- ── Contact form ───────────────────────────────── -->
            <div class="col-12 col-md-7">
                <div class="contact-form-wrap">
                    <h2>Nachricht senden</h2>

                    <div class="form">

                        <div class="form-group">
                            <label for="contact-name">Name</label>
                            <input type="text" id="contact-name" name="name"
                                placeholder="Ihr Name" required>
                        </div>

                        <div class="form-group">
                            <label for="contact-email">E-Mail</label>
                            <input type="email" id="contact-email" name="email"
                                placeholder="ihre@email.com" required>
                        </div>

                        <div class="form-group">
                            <label for="contact-message">Nachricht</label>
                            <textarea id="contact-message" name="message"
                                rows="6"
                                placeholder="Ihre Nachricht..." required></textarea>
                        </div>

                        <button type="submit" class="btn-section">
                            Senden
                        </button>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>