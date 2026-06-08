/**
 * app.js — kulturCMS
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Sidebar toggle ────────────────────────────────────────

    const hamburger = document.getElementById('hamburger');
    const sidebar = document.getElementById('sidebar');

    if (hamburger && sidebar) {
        hamburger.addEventListener('click', function () {
            const isOpen = sidebar.classList.toggle('open');
            hamburger.setAttribute('aria-expanded', isOpen);
        });

        // Close when a link is clicked
        sidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                sidebar.classList.remove('open');
                hamburger.setAttribute('aria-expanded', 'false');
            });
        });

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                sidebar.classList.remove('open');
                hamburger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // ── Edit mode ─────────────────────────────────────────────
    // document.body.classList.add('is-editing')    ← when edit form opens
    // document.body.classList.remove('is-editing') ← on save or cancel

});