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

    <a href="/logout" class="edit-bar__exit" title="Exit edit mode">
        Exit Edit Mode
        <i class="ti ti-logout"></i>
    </a>
</div>