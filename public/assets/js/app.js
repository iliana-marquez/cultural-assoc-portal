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
                        showFeedback(json.message ?? 'Nachricht gesendet!', 'success');
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
                        showFeedback(json.message ?? 'Bitte E-Mails prüfen!', 'success');
                        emailInput.value = '';
                        submitBtn.disabled = true;
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
});