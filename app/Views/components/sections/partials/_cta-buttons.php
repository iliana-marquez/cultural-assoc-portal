<?php

/**
 * components/sections/_cta-buttons.php
 *
 * Renders a section's call-to-action buttons — 0 to 3, editor's
 * choice. Sourced from entity_urls (entity_type='section'), the
 * exact same storage mechanism events/org/participants already
 * use for their own links — NOT the old single $cta object that
 * used to live inside the section's JSON content.
 *
 * Unlike entity-urls.php (a full editable ROW — header, pencil/
 * cancel toggle, its own list), this renders INLINE, alongside
 * the section's title/subtitle/text — a CTA is part of the
 * section's content, not a separate block with its own toggle
 * state. Editing affordances (pencil/trash per button, one add
 * trigger) are small and inline, matching that context.
 *
 * Required variables:
 *   $section        object  must have ->id
 *   $isLoggedIn     bool
 *   $ctaAlignClass  string  already computed by section.php, reused
 *                           so buttons align consistently with the
 *                           section's existing layout
 *
 * Provides its own $ctaUrls fetch — callers don't need to fetch
 * entity_urls themselves before including this.
 */

require_once __DIR__ . '/../../../../Models/UrlModel.php';
$ctaUrls = (new UrlModel())->getForEntity('section', $section->id);

$maxCtas = 3;
?>
<?php if (!empty($ctaUrls) || $isLoggedIn): ?>
    <div class="section-cta-row <?= $ctaAlignClass ?? 'align-self-start' ?>"
        data-entity-type="section" data-entity-id="<?= $section->id ?>">

        <?php foreach ($ctaUrls as $cta): ?>
            <span class="section-cta-item">
                <a href="<?= htmlspecialchars($cta->url) ?>" class="btn-section" target="_blank" rel="noopener noreferrer">
                    <?= htmlspecialchars($cta->cta_label ?? '') ?>
                </a>
                <?php if ($isLoggedIn): ?>
                    <button class="entity-edit-btn border-0 section-cta-edit"
                        data-action="edit-section-cta"
                        data-url-id="<?= $cta->id ?>"
                        data-url-type-id="<?= $cta->url_type_id ?>"
                        data-url-value="<?= htmlspecialchars($cta->url) ?>"
                        data-url-label="<?= htmlspecialchars($cta->cta_label ?? '') ?>">
                        <i class="ti ti-pencil"></i>
                    </button>
                    <button class="entity-remove-btn border-0 section-cta-remove"
                        data-action="remove-section-cta"
                        data-url-id="<?= $cta->id ?>">
                        <i class="ti ti-trash"></i>
                    </button>
                <?php endif; ?>
            </span>
        <?php endforeach; ?>

        <?php if ($isLoggedIn && count($ctaUrls) < $maxCtas): ?>
            <button class="entity-edit-btn section-cta-add" data-action="add-section-cta">
                <i class="ti ti-plus"></i> CTA
            </button>
        <?php endif; ?>

    </div>
<?php endif; ?>