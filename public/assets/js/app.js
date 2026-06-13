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


    // ── Edit mode ─────────────────────────────────────────────
    // document.body.classList.add('is-editing')    ← when edit form opens
    // document.body.classList.remove('is-editing') ← on save or cancel

});