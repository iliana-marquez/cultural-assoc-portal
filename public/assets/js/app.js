/**
 * app.js — kulturCMS
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Sidebar toggle ────────────────────────────────────────

    const hamburger = document.getElementById('hamburger');
    const sidebar = document.getElementById('sidebar');
    const icon = document.getElementById('hamburger-icon');

    if (hamburger && sidebar && icon) {

        hamburger.addEventListener('click', function () {
            const isOpen = sidebar.classList.toggle('open');

            hamburger.setAttribute('aria-expanded', isOpen);

            // toggle icon
            if (isOpen) {
                icon.classList.remove('ti-menu-2');
                icon.classList.add('ti-x');
                hamburger.setAttribute('aria-label', 'Menü schließen');
            } else {
                icon.classList.remove('ti-x');
                icon.classList.add('ti-menu-2');
                hamburger.setAttribute('aria-label', 'Menü öffnen');
            }
        });

        // Close on link click
        sidebar.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function () {
                sidebar.classList.remove('open');
                hamburger.setAttribute('aria-expanded', 'false');

                icon.classList.remove('ti-x');
                icon.classList.add('ti-menu-2');
                hamburger.setAttribute('aria-label', 'Menü öffnen');
            });
        });

        // Close on ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                sidebar.classList.remove('open');
                hamburger.setAttribute('aria-expanded', 'false');

                icon.classList.remove('ti-x');
                icon.classList.add('ti-menu-2');
                hamburger.setAttribute('aria-label', 'Menü öffnen');
            }
        });

        // Close sidebar automatically when width expands 
        window.addEventListener('resize', function () {
            // Checks if the window width passes your 1000px desktop threshold
            if (window.innerWidth > 1000) {
                // Remove the 'open' class to hide the sidebar via CSS
                sidebar.classList.remove('open');

                // Reset accessibility states and hamburger icon back to default
                hamburger.setAttribute('aria-expanded', 'false');
                icon.classList.remove('ti-x');
                icon.classList.add('ti-menu-2');
                hamburger.setAttribute('aria-label', 'Menü öffnen');
            }
        });
    }

    // ── Zoomable images — click opens full size in new tab ──────
    document.querySelectorAll('.zoomable').forEach(function (img) {
        img.addEventListener('click', function () {
            window.open(img.src, '_blank');
        });
    });



    // ── Contact form ──────────────────────────────────────────
    const contactForm = document.querySelector('[data-action="contact-form"]');
    if (contactForm) {
        const submitBtn = document.getElementById('contact-submit');
        const feedback = document.getElementById('contact-feedback');
        const nameInput = document.getElementById('contact-name');
        const emailInput = document.getElementById('contact-email');
        const msgInput = document.getElementById('contact-message');
        const csrfInput = document.getElementById('csrf-contact');

        const errorName = document.getElementById('error-name');
        const errorEmail = document.getElementById('error-email');
        const errorMessage = document.getElementById('error-message');
        const errorTerms = document.getElementById('error-terms');
        const termsInput = document.getElementById('contact-terms');

        // Track which fields the user has touched
        const touched = { name: false, email: false, message: false, terms: false };

        // Disable on init
        if (submitBtn) submitBtn.disabled = true;

        function showFeedback(message, type) {
            feedback.textContent = message;
            feedback.className = 'contact-feedback contact-feedback--' + type;
            feedback.style.display = 'block';
        }

        function showFieldError(el, msg) {
            if (el) { el.textContent = msg; el.style.display = msg ? 'block' : 'none'; }
        }

        function isValidEmail(val) {
            return /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/.test(val);
        }

        function hasInjection(val) {
            return /<|>|javascript:|on\w+\s*=/i.test(val);
        }

        function validateName(show) {
            const val = nameInput?.value.trim() ?? '';
            let err = '';
            if (!val) err = 'Bitte Namen eingeben.';
            else if (val.length < 2) err = 'Mindestens 2 Zeichen.';
            else if (val.length > 200) err = 'Maximal 200 Zeichen.';
            else if (hasInjection(val)) err = 'Ungültige Zeichen im Namen.';
            if (show) showFieldError(errorName, err);
            return !err;
        }

        function validateEmail(show) {
            const val = emailInput?.value.trim() ?? '';
            let err = '';
            if (!val) err = 'Bitte E-Mail eingeben.';
            else if (!isValidEmail(val)) err = 'Bitte eine gültige E-Mail-Adresse eingeben.';
            else if (val.length > 200) err = 'E-Mail zu lang.';
            if (show) showFieldError(errorEmail, err);
            return !err;
        }

        function validateMessage(show) {
            const val = msgInput?.value.trim() ?? '';
            let err = '';
            if (!val) err = 'Bitte Nachricht eingeben.';
            else if (val.length < 10) err = 'Mindestens 10 Zeichen.';
            else if (val.length > 5000) err = 'Maximal 5000 Zeichen.';
            else if (hasInjection(val)) err = 'Ungültige Zeichen in der Nachricht.';
            if (show) showFieldError(errorMessage, err);
            return !err;
        }

        function validateTerms(show) {
            const checked = termsInput?.checked ?? false;
            if (show && !checked) showFieldError(errorTerms, 'Bitte Datenschutz bestätigen.');
            else if (show) showFieldError(errorTerms, '');
            return checked;
        }

        function updateSubmitState() {
            const valid = validateName(false) && validateEmail(false)
                && validateMessage(false);
            if (submitBtn) submitBtn.disabled = !valid;
        }

        // Show error only after field is touched (on blur)
        nameInput?.addEventListener('blur', function () {
            touched.name = true;
            validateName(true);
            updateSubmitState();
        });
        emailInput?.addEventListener('blur', function () {
            touched.email = true;
            validateEmail(true);
            updateSubmitState();
        });
        msgInput?.addEventListener('blur', function () {
            touched.message = true;
            validateMessage(true);
            updateSubmitState();
        });
        termsInput?.addEventListener('change', function () {
            touched.terms = true;
            validateTerms(true);
            updateSubmitState();
        });

        // Update button state on input without showing errors
        nameInput?.addEventListener('input', function () { if (touched.name) validateName(true); updateSubmitState(); });
        emailInput?.addEventListener('input', function () { if (touched.email) validateEmail(true); updateSubmitState(); });
        msgInput?.addEventListener('input', function () { if (touched.message) validateMessage(true); updateSubmitState(); });

        submitBtn?.addEventListener('click', function () {
            // Show all errors on submit attempt
            touched.name = touched.email = touched.message = touched.terms = true;
            const valid = validateName(true) && validateEmail(true)
                && validateMessage(true) && validateTerms(true);
            if (!valid) return;

            submitBtn.disabled = true;
            submitBtn.textContent = 'Wird gesendet...';

            const data = new FormData();
            data.append('name', nameInput.value.trim());
            data.append('email', emailInput.value.trim());
            data.append('message', msgInput.value.trim());
            data.append('csrf_contact', csrfInput?.value ?? '');

            fetch('/kontakt', {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.json())
                .then(function (json) {
                    if (json.success) {
                        nameInput.value = '';
                        emailInput.value = '';
                        msgInput.value = '';
                        if (termsInput) termsInput.checked = false;
                        showFieldError(errorName, '');
                        showFieldError(errorEmail, '');
                        showFieldError(errorMessage, '');
                        showFieldError(errorTerms, '');
                        touched.name = touched.email = touched.message = touched.terms = false;
                        submitBtn.disabled = true;
                        openFeedbackModal({
                            title: 'Vielen Dank für Ihre Nachricht!',
                            message: 'Wir melden uns so bald wie möglich bei Ihnen.'
                        });
                    } else {
                        showFeedback(json.error ?? 'Fehler beim Senden.', 'error');
                        updateSubmitState();
                    }
                })
                .catch(function () {
                    showFeedback('Verbindungsfehler. Bitte versuche es später erneut.', 'error');
                    updateSubmitState();
                })
                .finally(function () {
                    submitBtn.textContent = 'Senden';
                });
        });
    }


    // ── Newsletter subscribe ──────────────────────────────────
    const newsletterForm = document.querySelector('[data-action="newsletter-subscribe"]');
    if (newsletterForm) {
        const emailInput = document.getElementById('newsletter-email');
        const submitBtn = document.getElementById('newsletter-submit');
        const feedback = document.getElementById('newsletter-feedback');
        const csrfInput = document.getElementById('csrf-newsletter');

        function isValidEmail(val) {
            return /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/.test(val);
        }

        function showFeedback(message, type) {
            feedback.textContent = message;
            feedback.className = 'newsletter-strip__feedback newsletter-strip__feedback--' + type;
            feedback.style.display = 'block';
        }

        emailInput?.addEventListener('input', function () {
            if (submitBtn) submitBtn.disabled = !isValidEmail(emailInput.value.trim());
        });

        if (submitBtn) submitBtn.disabled = true;

        submitBtn?.addEventListener('click', function () {
            const email = emailInput?.value.trim() ?? '';
            if (!isValidEmail(email)) return;

            submitBtn.disabled = true;
            submitBtn.textContent = '...';

            const data = new FormData();
            data.append('email', email);
            data.append('csrf_newsletter', csrfInput?.value ?? '');

            fetch('/newsletter/subscribe', {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.json())
                .then(function (json) {
                    if (json.success) {
                        emailInput.value = '';
                        submitBtn.disabled = true;
                        openFeedbackModal({
                            title: 'Vielen Dank!',
                            message: 'Wir haben Ihnen eine Bestätigungs-E-Mail geschickt. Bitte klicken Sie auf den Link darin — wir freuen uns, Sie bald mit unserem Programm zu begeistern!'
                        });
                    } else {
                        showFeedback(json.error ?? 'Fehler. Bitte erneut versuchen.', 'error');
                        submitBtn.disabled = false;
                    }
                })
                .catch(function () {
                    showFeedback('Verbindungsfehler.', 'error');
                    submitBtn.disabled = false;
                })
                .finally(function () {
                    submitBtn.textContent = 'Anmelden';
                });
        });
    }

    // ── Membership request form ───────────────────────────────
    const membershipForm = document.querySelector('[data-action="membership-request-form"]');
    if (membershipForm) {
        const submitBtn = document.getElementById('membership-submit');
        const feedback = document.getElementById('membership-feedback');
        const firstNameInput = document.getElementById('membership-first-name');
        const lastNameInput = document.getElementById('membership-last-name');
        const emailInput = document.getElementById('membership-email');
        const termsInput = document.getElementById('membership-terms');
        const csrfInput = document.getElementById('csrf-mitglied');

        const errorFirstName = document.getElementById('membership-error-first-name');
        const errorLastName = document.getElementById('membership-error-last-name');
        const errorEmail = document.getElementById('membership-error-email');
        const errorTerms = document.getElementById('membership-error-terms');
        const errorMitglied = document.getElementById('membership-error-mitglied');

        const touched = { firstName: false, lastName: false, email: false, terms: false };

        function showFeedback(message, type) {
            if (!feedback) return;
            feedback.textContent = message;
            feedback.className = 'contact-feedback contact-feedback--' + type;
            feedback.style.display = 'block';
        }

        function showFieldError(el, msg) {
            if (el) { el.textContent = msg; el.style.display = msg ? 'block' : 'none'; }
        }

        function isValidEmail(val) {
            return /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/.test(val);
        }

        function validateFirstName(show) {
            const val = firstNameInput?.value.trim() ?? '';
            let err = '';
            if (!val) err = 'Bitte Vorname eingeben.';
            else if (val.length < 2) err = 'Mindestens 2 Zeichen.';
            else if (val.length > 100) err = 'Maximal 100 Zeichen.';
            if (show) showFieldError(errorFirstName, err);
            return !err;
        }

        function validateLastName(show) {
            const val = lastNameInput?.value.trim() ?? '';
            let err = '';
            if (!val) err = 'Bitte Nachname eingeben.';
            else if (val.length < 2) err = 'Mindestens 2 Zeichen.';
            else if (val.length > 100) err = 'Maximal 100 Zeichen.';
            if (show) showFieldError(errorLastName, err);
            return !err;
        }

        function validateEmail(show) {
            const val = emailInput?.value.trim() ?? '';
            let err = '';
            if (!val) err = 'Bitte E-Mail eingeben.';
            else if (!isValidEmail(val)) err = 'Bitte eine gültige E-Mail-Adresse eingeben.';
            if (show) showFieldError(errorEmail, err);
            return !err;
        }

        function validateTerms(show) {
            const checked = termsInput?.checked ?? false;
            if (show && !checked) showFieldError(errorTerms, 'Bitte Datenschutz bestätigen.');
            else if (show) showFieldError(errorTerms, '');
            return checked;
        }

        function validateMitglied(show) {
            const checked = document.getElementById('membership-check')?.checked ?? false;
            if (show && !checked) showFieldError(errorMitglied, 'Bitte Mitgliedschaft bestätigen.');
            else if (show) showFieldError(errorMitglied, '');
            return checked;
        }

        // Live validation after field is touched
        firstNameInput?.addEventListener('blur', function () { touched.firstName = true; validateFirstName(true); });
        lastNameInput?.addEventListener('blur', function () { touched.lastName = true; validateLastName(true); });
        emailInput?.addEventListener('blur', function () { touched.email = true; validateEmail(true); });
        document.getElementById('membership-check')?.addEventListener('change', function () { validateMitglied(true); });
        termsInput?.addEventListener('change', function () { touched.terms = true; validateTerms(true); });
        firstNameInput?.addEventListener('input', function () { if (touched.firstName) validateFirstName(true); });
        lastNameInput?.addEventListener('input', function () { if (touched.lastName) validateLastName(true); });
        emailInput?.addEventListener('input', function () { if (touched.email) validateEmail(true); });

        submitBtn?.addEventListener('click', function () {
            // Force all touched states and show all field errors
            touched.firstName = touched.lastName = touched.email = touched.terms = true;
            // const valid = validateFirstName(true) && validateLastName(true) && validateEmail(true) && validateTerms(true) && validateMitglied(true);
            // if (!valid) return;

            if (!validateMitglied(true)) return;
            if (!validateFirstName(true)) return;
            if (!validateLastName(true)) return;
            if (!validateEmail(true)) return;
            if (!validateTerms(true)) return;

            submitBtn.disabled = true;
            submitBtn.textContent = 'Wird gesendet...';

            const data = new FormData();
            data.append('first_name', firstNameInput.value.trim());
            data.append('last_name', lastNameInput.value.trim());
            data.append('email', emailInput.value.trim());
            data.append('adresse', document.getElementById('membership-adresse')?.value.trim() ?? '');
            data.append('plz', document.getElementById('membership-plz')?.value.trim() ?? '');
            data.append('ort', document.getElementById('membership-ort')?.value.trim() ?? '');
            data.append('telefon', document.getElementById('membership-phone')?.value.trim() ?? '');
            data.append('geburtstag', document.getElementById('membership-birth-date')?.value ?? '');
            data.append('mitglied', document.getElementById('membership-check')?.checked ? '1' : '');
            data.append('newsletter', document.getElementById('newsletter-check')?.checked ? '1' : '');
            data.append('csrf_membership', csrfInput?.value ?? '');

            fetch('/mitglied-werden', {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.json())
                .then(function (json) {
                    if (json.success) {
                        membershipForm.reset();
                        submitBtn.disabled = true;
                        openFeedbackModal({
                            title: 'Schön, dass Sie dabei sind!',
                            message: 'Ihr Antrag ist bei uns eingegangen.\nSie erhalten in Kürze eine E-Mail mit allen Informationen zur Zahlung des Mitgliedsbeitrags.'
                        });
                    } else {
                        showFeedback(json.error ?? 'Fehler beim Senden.', 'error');
                        submitBtn.disabled = false;
                    }
                })
                .catch(function () {
                    showFeedback('Verbindungsfehler. Bitte versuche es später erneut.', 'error');
                    submitBtn.disabled = false;
                })
                .finally(function () {
                    submitBtn.textContent = 'Mitglied werden';
                });
        });
    }

    // ── Archive filter ────────────────────────────────────────────
    // Year chips and category chips filter the archive grid via AJAX.
    // Only the event grid (#archive-grid) and category chips
    // (#archive-categories) are swapped — the year timeline is static
    // PHP and survives every filter call without re-rendering.
    // URL updates via history.pushState so filtered views are shareable.

    (function () {
        const timeline = document.querySelector('.archive-timeline');
        if (!timeline) return; // only runs on /archiv

        let currentYear = timeline.querySelector('.archive-year-chip--active')?.dataset.year ?? null;
        let currentCategory = null;

        // Initialise category from URL on page load (shareable URL support)
        const initParams = new URLSearchParams(window.location.search);
        if (initParams.get('category')) {
            currentCategory = initParams.get('category');
        }

        function filter(year, category) {
            const params = new URLSearchParams({ year });
            if (category) params.set('category', category);

            // Update browser URL without reload
            history.pushState({ year, category }, '', '/archiv?' + params.toString());

            // Update active state on year chips
            timeline.querySelectorAll('.archive-year-chip').forEach(function (chip) {
                chip.classList.toggle('archive-year-chip--active', chip.dataset.year == year);
            });

            fetch('/archiv/filter?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    if (!json.success) return;
                    document.getElementById('archive-categories').innerHTML = json.categories;
                    document.getElementById('archive-grid').innerHTML = json.events;
                    bindCategoryChips();
                })
                .catch(function () {
                    // Silent fail — grid stays as-is
                });
        }

        function bindCategoryChips() {
            document.querySelectorAll('.archive-category-chip').forEach(function (chip) {
                chip.addEventListener('click', function (e) {
                    e.preventDefault();
                    // Toggle: clicking active category resets to "all"
                    const cat = chip.dataset.category || null;
                    currentCategory = (cat === currentCategory) ? null : cat;
                    filter(currentYear, currentCategory);
                });
            });
        }

        // Year chip clicks
        timeline.querySelectorAll('.archive-year-chip').forEach(function (chip) {
            chip.addEventListener('click', function (e) {
                e.preventDefault();
                currentYear = chip.dataset.year;
                currentCategory = null; // reset category on year change
                filter(currentYear, null);
            });
        });

        // Initial category chip binding (PHP-rendered on page load)
        bindCategoryChips();

        // Browser back/forward — restore filter state from URL
        window.addEventListener('popstate', function (e) {
            const state = e.state;
            if (state && state.year) {
                currentYear = state.year;
                currentCategory = state.category || null;
                filter(currentYear, currentCategory);
            }
        });

    })();

});