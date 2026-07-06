<?php

/**
 * unterstuetzer.php
 *
 * Public listing of contributors — Partners, Sponsors, Supporters, Institutions.
 * Free sections above and below via render-sections.php.
 * Contributor cards via contributor-card.php partial.
 * Same grouped layout for both editor and public — edit controls shown when logged in.
 *
 * $contributors, $sections, $isLoggedIn available from ContributorController.
 */

function editRow(string $label, string $field, string $value, string $saveUrl): string
{
    return '
    <div class="entity-edit-row" data-save-url="' . htmlspecialchars($saveUrl) . '">
        <div class="edit-row-header">
            <label class="edit-row-label">' . htmlspecialchars($label) . '</label>
            <div class="edit-row-actions">
                <span class="entity-feedback"></span>
                <button class="entity-edit-btn"><i class="ti ti-pencil"></i></button>
                <button class="entity-save-btn"><i class="ti ti-check"></i></button>
                <button class="entity-cancel-btn"><i class="ti ti-x"></i></button>
            </div>
        </div>
        <span class="entity-field" data-field="' . htmlspecialchars($field) . '">' . htmlspecialchars($value) . '</span>
    </div>';
}

$sectionsMode = 'intro';
require __DIR__ . '/../components/sections/render-sections.php';
?>

<section class="segment light-segment">
    <div class="container">

        <?php if ($isLoggedIn): ?>
            <div class="entity-add-row">
                <input type="text" id="contributor-name-input"
                    placeholder="Name des Unterstützers"
                    class="entity-add-input">
                <button class="btn-section" id="contributor-add-btn">
                    <i class="ti ti-plus"></i> Unterstützer hinzufügen
                </button>
            </div>
        <?php endif; ?>

        <?php if (empty($contributors)): ?>
            <?php if ($isLoggedIn): ?>
                <p class="text-muted mt-4">Noch keine Unterstützer. Füge den ersten hinzu.</p>
            <?php else: ?>
                <p class="text-muted mt-4">Inhalt kommt in Kürze.</p>
            <?php endif; ?>

        <?php else: ?>
            <?php
            $types = [
                'partner'       => 'Partner',
                'foerderer'     => 'Förderer',
                'unterstuetzer' => 'Unterstützer',
                'institution'   => 'Institutionen',
            ];

            foreach ($types as $typeKey => $typeLabel):
                $group = array_filter($contributors, fn($c) => ($c->type ?? '') === $typeKey);
                if (empty($group)) continue;
            ?>
                <div class="contributor-group">
                    <h3 class="mb-4"><?= $typeLabel ?></h3>
                    <?php foreach ($group as $contributor): ?>
                        <?php $saveUrl = '/contributors/' . $contributor->id . '/save'; ?>
                        <?php require __DIR__ . '/../components/contributor-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <?php
            $untyped = array_filter($contributors, fn($c) => empty($c->type));
            if (!empty($untyped)):
            ?>
                <div class="contributor-group">
                    <?php foreach ($untyped as $contributor): ?>
                        <?php $saveUrl = '/contributors/' . $contributor->id . '/save'; ?>
                        <?php require __DIR__ . '/../components/contributor-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</section>

<?php
$sectionsMode = 'rest';
require __DIR__ . '/../components/sections/render-sections.php';
?>