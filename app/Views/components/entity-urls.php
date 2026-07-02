<?php

/**
 * components/entity-urls.php
 *
 * Renders everything related to the urls belonging to whichever
 * entity is hosting this block — events, participants, organisation
 * info, free-page sections, and any future entity that links urls
 * the same way. One file, toggled entirely by $isLoggedIn:
 *
 *   - Logged out: the public-facing list (icon + label, clickable)
 *   - Logged in:  the full edit-row UI — header, pencil/cancel
 *                 toggle, the editable list, and an add-link trigger
 *
 * Deliberately NOT split across separate header/list/item files —
 * editing and viewing are one continuous experience in this product,
 * not two separable concerns, so the markup for both lives in one
 * place. The fragment endpoint (UrlController::fragment()) re-renders
 * this SAME file and the JS swaps only the .links-list-container's
 * innerHTML — that DOM-level targeting, not a PHP file boundary, is
 * what protects the header's pencil/cancel button listeners from
 * being destroyed on every add/edit/remove.
 *
 * Any visual difference between contexts (e.g. a more compact,
 * icon-only treatment on one page) is a CSS concern, scoped to
 * that page/context — this partial only ever renders one honest
 * structure, never a second "style mode".
 *
 * Required variables:
 *   $entityType    string  'event' | 'participant' | 'organisation' | ...
 *   $entityId      int
 *   $urls          array   from UrlModel::getForEntity($entityType, $entityId)
 *   $isLoggedIn    bool
 *   $fragmentOnly  bool    optional, default false. When true, renders
 *                          ONLY the inner list/empty-state markup —
 *                          no header, no wrapper, no add-button. Used
 *                          by UrlController::fragment(), since the JS
 *                          only ever replaces .links-list-container's
 *                          INNER content, never the row itself.
 */
$fragmentOnly = $fragmentOnly ?? false;

if ($fragmentOnly):
?>
    <?php if (!empty($urls)): ?>
        <div class="event-url-list p-2">
            <?php foreach ($urls as $url): ?>
                <div class="event-url-item" data-url-id="<?= $url->id ?>">
                    <?php if ($isLoggedIn): ?>
                        <button class="entity-edit-btn border-0"
                            data-action="edit-entity-url"
                            data-url-id="<?= $url->id ?>"
                            data-url-type-id="<?= $url->url_type_id ?>"
                            data-url-value="<?= htmlspecialchars($url->url) ?>"
                            data-url-label="<?= htmlspecialchars($url->label ?? '') ?>">
                            <i class="ti ti-pencil"></i>
                        </button>
                        <button class="entity-remove-btn border-0"
                            data-action="remove-entity-url"
                            data-url-id="<?= $url->id ?>">
                            <i class="ti ti-trash"></i>
                        </button>
                    <?php endif; ?>
                    <a href="<?= htmlspecialchars($url->url) ?>" target="_blank" rel="noopener">
                        <i class="ti <?= htmlspecialchars($url->icon ?? 'ti-link') ?>"></i>
                        <?= htmlspecialchars($url->label ?: $url->type_label) ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif ($isLoggedIn): ?>
        <div class="event-url-list p-2">
            <p class="text-muted p-2 mb-0">
                <i class="ti ti-link-off"></i>
                Noch keine Links
            </p>
        </div>
    <?php endif; ?>
<?php
    return;
endif;
?>
<div class="event-urls links-edit-row" data-entity-type="<?= htmlspecialchars($entityType) ?>" data-entity-id="<?= (int) $entityId ?>">
    <?php if ($isLoggedIn): ?>
        <div class="edit-row-header">
            <label class="edit-row-label">Links</label>
            <div class="edit-row-actions">
                <span class="entity-feedback"></span>
                <button class="entity-edit-btn links-pencil-btn"><i class="ti ti-pencil"></i></button>
                <button class="entity-cancel-btn links-cancel-btn"><i class="ti ti-x"></i></button>
            </div>
        </div>
    <?php elseif (!empty($urls)): ?>
        <h3>Links</h3>
    <?php endif; ?>

    <div class="links-list-container">
        <?php if (!empty($urls)): ?>
            <div class="event-url-list p-2">
                <?php foreach ($urls as $url): ?>
                    <div class="event-url-item" data-url-id="<?= $url->id ?>">
                        <?php if ($isLoggedIn): ?>
                            <button class="entity-edit-btn border-0"
                                data-action="edit-entity-url"
                                data-url-id="<?= $url->id ?>"
                                data-url-type-id="<?= $url->url_type_id ?>"
                                data-url-value="<?= htmlspecialchars($url->url) ?>"
                                data-url-label="<?= htmlspecialchars($url->label ?? '') ?>">
                                <i class="ti ti-pencil"></i>
                            </button>
                            <button class="entity-remove-btn border-0"
                                data-action="remove-entity-url"
                                data-url-id="<?= $url->id ?>">
                                <i class="ti ti-trash"></i>
                            </button>
                        <?php endif; ?>
                        <a href="<?= htmlspecialchars($url->url) ?>" target="_blank" rel="noopener" class="nav-icon-ux">
                            <i class="ti <?= htmlspecialchars($url->icon ?? 'ti-link') ?>"></i>
                            <?= htmlspecialchars($url->label ?: $url->type_label) ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($isLoggedIn): ?>
            <div class="event-url-list p-2">
                <p class="text-muted p-2 mb-0">
                    <i class="ti ti-link-off"></i>
                    Noch keine Links
                </p>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($isLoggedIn): ?>
        <div class="add-link-wrap p-2">
            <button class="entity-edit-btn" data-action="add-entity-url">
                <i class="ti ti-plus"></i> Link hinzufügen
            </button>
        </div>
    <?php endif; ?>
</div>