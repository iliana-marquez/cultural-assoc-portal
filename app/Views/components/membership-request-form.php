<?php if (!empty($org->membership_fee)): ?>
    <?php
    $fee = (float) $org->membership_fee;
    $feeFormatted = (floor($fee) == $fee) ? number_format($fee, 0, ',', '.') : number_format($fee, 2, ',', '.');
    ?>

    <section class="segment light-segment" id="membership-request-form">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10 col-xl-8">

                    <h2>Jetzt Mitglied werden</h2>
                    <hr>

                    <form class="membership-request-form" data-action="membership-request-form">

                        <input type="hidden" id="csrf-mitglied" name="csrf_membership"
                            value="<?= htmlspecialchars($csrf_membership ?? '') ?>">

                        <div class="row">

                            <!-- Membership -->
                            <div class="col-12">
                                <label class="membership-option g-1">
                                    <input type="checkbox"
                                        id="membership-check"
                                        name="mitglied"
                                        value="1">
                                    <span>
                                        Ja, ich möchte <strong>Mitglied</strong> bei <?= htmlspecialchars($org->name ?? '') ?> werden
                                        <?php if (!empty($org->membership_fee)): ?>
                                            | Jahresmitgliedsbeitrag: <strong><?= htmlspecialchars($org->membership_fee) ?></strong> <i class="ti ti-asterisk required-icon"></i>
                                        <?php endif; ?>
                                    </span><br>
                                    <span class="field-error" id="membership-error-mitglied"></span>
                                </label>
                                <div class="membership-note">
                                    <?php if (!empty($org->membership_note)): ?>
                                        <small><i class="ti ti-asterisk required-icon"></i><?= htmlspecialchars($org->membership_note) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Newsletter -->
                            <div class="col-12 mt-2">
                                <label class="membership-option">
                                    <input type="checkbox"
                                        id="newsletter-check"
                                        name="newsletter"
                                        value="1">

                                    <span>
                                        Ja, ich möchte den <strong>Newsletter</strong> abonnieren und über Neuigkeiten sowie Veranstaltungen informiert werden.
                                    </span>
                                </label>
                            </div>

                            <!-- First Name + Last Name -->
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="membership-first-name">Vorname <i class="ti ti-asterisk required-icon"></i></label>
                                    <input type="text" id="membership-first-name" name="first_name"
                                        placeholder="Vorname"
                                        required minlength="2" maxlength="100"
                                        autocomplete="given-name">
                                    <span class="field-error" id="membership-error-first-name"></span>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="membership-last-name">Nachname <i class="ti ti-asterisk required-icon"></i></label>
                                    <input type="text" id="membership-last-name" name="last_name"
                                        placeholder="Nachname"
                                        required minlength="2" maxlength="100"
                                        autocomplete="family-name">
                                    <span class="field-error" id="membership-error-last-name"></span>
                                </div>
                            </div>

                            <!-- Birthday -->
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="mitglied-geburtstag">Geburtstag</label>
                                    <input type="date" id="membership-birth-date" name="geburtstag"
                                        autocomplete="bday">
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="mitglied-email">E-Mail <i class="ti ti-asterisk required-icon"></i></label>
                                    <input type="email" id="membership-email" name="email"
                                        placeholder="ihre@email.com"
                                        required maxlength="200"
                                        autocomplete="email">
                                    <span class="field-error" id="membership-error-email"></span>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="mitglied-telefon">Telefon</label>
                                    <input type="tel" id="membership-phone" name="telefon"
                                        placeholder="+43 ..."
                                        maxlength="50"
                                        autocomplete="tel">
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="mitglied-adresse">Adresse</label>
                                    <input type="text" id="membership-adresse" name="adresse"
                                        placeholder="Straße und Hausnummer"
                                        maxlength="200"
                                        autocomplete="street-address">
                                    <span class="field-error" id="membership-error-adresse"></span>
                                </div>
                            </div>

                            <!-- ZIP -->
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="mitglied-plz">PLZ</label>
                                    <input type="text" id="membership-plz" name="plz"
                                        placeholder="1090"
                                        maxlength="10"
                                        autocomplete="postal-code">
                                </div>
                            </div>

                            <!-- City -->
                            <div class="col-12 col-md-9">
                                <div class="form-group">
                                    <label for="mitglied-ort">Ort</label>
                                    <input type="text" id="membership-ort" name="ort"
                                        placeholder="Wien"
                                        maxlength="100"
                                        autocomplete="address-level2">
                                </div>
                            </div>

                            <!-- Privacy -->
                            <div class="col-12 d-flex mt-2">
                                <label class="membership-terms">
                                    <input type="checkbox" id="membership-terms" required>
                                    <span>
                                        Ich bin damit einverstanden, dass meine Daten für die
                                        Mitgliedschaft verarbeitet werden.
                                        <span class="d-flex flex-row">
                                            <a href="/datenschutz"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-link disclaimer">
                                                Datenschutzerklärung <i class="ti ti-asterisk required-icon"></i>
                                            </a>
                                            <span class="field-error" id="membership-error-terms"></span>
                                        </span>
                                    </span>
                                </label>
                            </div>

                            <!-- Required Fields -->
                            <div class="col-12 mt-2">
                                <small><i class="ti ti-asterisk required-icon"></i>Pflichtfelder</small>
                            </div>

                            <!-- Button -->
                            <div class="col-12 text-md-end">
                                <button type="button"
                                    class="btn-section"
                                    id="membership-submit">
                                    Mitglied werden
                                </button>
                            </div>

                            <!-- Feedback -->
                            <div class="col-12">
                                <p class="contact-feedback"
                                    id="membership-feedback"
                                    style="display:none;"></p>
                            </div>


                        </div>

                    </form>

                </div>
            </div>
        </div>

    </section>

<?php else: ?>
    <section class="segment light-segment">
        <p class="text-center">Inhalt folgt in Kürze.</p>
    </section>
<?php endif; ?>