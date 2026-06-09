<?php

/**
 * footer.php
 *
 * Site footer component.
 * Populate from organisation_info via OrganisationModel
 * (name, address, email, social links)
 */
?>

<footer class="text-center mt-auto dark-segment">
    <p>
        <?= htmlspecialchars($org->name ?? '') ?> | <?= htmlspecialchars($org->email ?? '') ?> | <?= htmlspecialchars($org->city ?? '') ?>
    </p>
    <p>
        Powered by <a href="https://ilianamarquez.com" target="_blank">OWS</a>
    </p>
</footer>