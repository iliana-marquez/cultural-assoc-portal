<?php

/**
 * contact.php
 *
 * Contact page — display only.
 * Org data edited via /wkk/org (edit bar link).
 */
?>

<?php $sectionsMode = 'intro'; ?>
<?php require __DIR__ . '/../components/sections/render-sections.php'; ?>

<section class="segment light-segment">
    <div class="container">
        <div class="row g-5">

            <!-- Contact info -->
            <div class="col-12 col-md-5">

                <!-- Email -->
                <?php if (!empty($org->email)): ?>
                    <div class="contact-block">
                        <h3>Anfragen &amp; Feedback</h3>
                        <a href="mailto:<?= htmlspecialchars($org->email) ?>" class="contact-link">
                            <i class="ti ti-mail"></i>
                            <?= htmlspecialchars($org->email) ?>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Address -->
                <?php if (!empty($org->name)): ?>
                    <div class="contact-block">
                        <h3>Veranstalter</h3>
                        <p><?= htmlspecialchars($org->name) ?></p>
                        <?php if (!empty($org->street)): ?>
                            <p class="contact-address">
                                <i class="ti ti-map-pin"></i>
                                <?= htmlspecialchars($org->street) ?>,
                                <?= htmlspecialchars($org->postcode) ?>
                                <?= htmlspecialchars($org->city) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Social links -->
                <?php if (!empty($urls)): ?>
                    <div class="contact-block">
                        <nav class="nav-socials" aria-label="Social Media">
                            <?php foreach ($urls as $url): ?>
                                <a href="<?= htmlspecialchars($url->url) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    title="<?= htmlspecialchars($url->type_label) ?>">
                                    <i class="ti <?= htmlspecialchars($url->icon) ?>"></i>
                                </a>
                            <?php endforeach; ?>
                        </nav>
                    </div>
                <?php endif; ?>

                <!-- ZVR -->
                <?php if (!empty($org->registration_number)): ?>
                    <div class="contact-block">
                        <small>ZVR: <?= htmlspecialchars($org->registration_number) ?></small>
                    </div>
                <?php endif; ?>

                <!-- Credit -->
                <div class="contact-block contact-credit">

                    <h3>Websitegestaltung</h3>
                    <a href="https://ilianamarquez.com" target="_blank" rel="noopener noreferrer">
                        Iliana Márquez
                        <small>(2026)</small>
                    </a>

                </div>

            </div>

            <!-- Contact form -->
            <div class="col-12 col-md-7">
                <div class="contact-form-wrap">
                    <h3>Nachricht senden</h3>

                    <div class="contact-form" data-action="contact-form">
                        <input type="hidden" id="csrf-contact" name="csrf_contact" value="<?= htmlspecialchars($csrf_contact ?? '') ?>">

                        <div class="form-group">
                            <label for="contact-name">Name</label>
                            <input type="text" id="contact-name" name="name"
                                placeholder="Ihr Name"
                                required minlength="2" maxlength="200"
                                autocomplete="name">
                            <span class="field-error" id="error-name"></span>
                        </div>

                        <div class="form-group">
                            <label for="contact-email">E-Mail</label>
                            <input type="email" id="contact-email" name="email"
                                placeholder="ihre@email.com"
                                required maxlength="200"
                                autocomplete="email">
                            <span class="field-error" id="error-email"></span>
                        </div>

                        <div class="form-group">
                            <label for="contact-message">Nachricht</label>
                            <textarea id="contact-message" name="message"
                                rows="6"
                                placeholder="Ihre Nachricht..."
                                required minlength="10" maxlength="5000"></textarea>
                            <span class="field-error" id="error-message"></span>
                        </div>

                        <div>
                            <label class="contact-terms">
                                <input type="checkbox" id="contact-terms" required>
                                <a href="/datenschutz" target="_blank" rel="noopener noreferrer">
                                    <span>
                                        Ich bin damit einverstanden, dass meine Daten für den
                                        angegebenen Zweck verwendet werden (Datenschutzerklärung).
                                    </span>
                                </a>

                            </label>
                            <span class="field-error" id="error-terms"></span>
                        </div>

                        <button type="button" class="btn-section" id="contact-submit">
                            Senden
                        </button>

                        <p class="contact-feedback" id="contact-feedback" style="display:none;"></p>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php $sectionsMode = 'rest'; ?>
<?php require __DIR__ . '/../components/sections/render-sections.php'; ?>