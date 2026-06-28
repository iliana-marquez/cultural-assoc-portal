<?php

/**
 * edit-bar.php
 *
 * Edit mode indicator bar — only rendered for logged-in editors.
 * Never rendered for visitors — not hidden with CSS, not in DOM at all.
 *
 * States (JS driven):
 *   default        → paused (grey, static indicator)
 *   body.is-editing → active (green, pulsing indicator)
 *
 * JS sets body.is-editing when an edit form is open.
 * Removed when form is saved or cancelled.
 */
?>

<div class="edit-bar text-center" id="edit-bar">
    <span class="edit-bar__indicator">
        <i class="ti ti-circle-filled"></i>
        <span class="edit-bar__label">Editing</span>
    </span>



    <div class="d-flex justify-content-end gap-3">
        <a href="#" class="edit-bar__exit" data-action="new-event">
            <i class="ti ti-plus"></i>
            Neue Veranstaltung
        </a>
        <span class="edit-bar__exit">|</span>

        <a href="#" class="edit-bar__exit" data-action="new-participant">
            <i class="ti ti-plus"></i>
            Neue:r Künstler:in
        </a>
        <span class="edit-bar__exit">|</span>

        <a href="#" class="edit-bar__exit" data-action="new-team">
            <i class="ti ti-plus"></i>
            Neues Teammitglied
        </a>
        <span class="edit-bar__exit">|</span>

        <a href="/<?= htmlspecialchars($config['admin_path']) ?>/org"
            class="edit-bar__exit" title="Exit edit mode">
            <i class="ti ti-edit"></i>
            Vereinsinfo
        </a>
        <span class="edit-bar__exit">|</span>

        <a href="/newsletter/subscribers"
            class="edit-bar__exit" title="Exit edit mode">
            <i class="ti ti-users-group"></i>
            Abonnenten
        </a>
        <span class="edit-bar__exit">|</span>

        <a href="/logout" class="edit-bar__exit nav-icon-ux">
            Exit Edit Mode
            <i class="ti ti-logout"></i>
        </a>
    </div>

</div>