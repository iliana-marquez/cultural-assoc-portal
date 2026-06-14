<?php

/**
 * participants.php
 * Participant listing — flat list with type label.
 */
?>

<?php if (!empty($sections)): ?>
    <?php require __DIR__ . '/../components/sections/render-sections.php'; ?>
<?php endif; ?>

<section class="segment light-segment">
    <div class="container">

        <?php if (empty($participants)): ?>
            <p>Inhalt folgt in Kürze.</p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($participants as $participant): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="/kuenstlerinnen/<?= htmlspecialchars($participant->slug) ?>"
                            class="team-card">

                            <div class="img-placeholder portrait-img">
                                <?php if (!empty($participant->promo)): ?>
                                    <img src="<?= htmlspecialchars($participant->promo->media_url) ?>"
                                        alt="<?= htmlspecialchars($participant->displayName) ?>">
                                <?php else: ?>
                                    <i class="ti ti-user"></i>
                                <?php endif; ?>
                            </div>

                            <div class="team-card__content">
                                <h3><?= htmlspecialchars($participant->displayName) ?></h3>
                                <?php if (!empty($participant->field)): ?>
                                    <p class="team-card__role">
                                        <?= htmlspecialchars($participant->field) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($participant->type) && $participant->type !== 'individual'): ?>
                                    <small><?= htmlspecialchars(ucfirst($participant->type)) ?></small>
                                <?php endif; ?>
                            </div>

                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>