<?php

/**
 * render-sections.php
 *
 * Reads sections array and renders each via the correct component,
 * with "+ Abschnitt hinzufügen" triggers between every consecutive
 * pair (and before the first / after the last) when logged in.
 *
 * Section types:
 *   hero    → components/hero.php (pulls from $org)
 *   section → components/sections/section.php (from JSON content)
 *
 * Two modes, via the optional $sectionsMode variable:
 *
 *   'rest' (default) — every section with order_index >= 1 (or,
 *     for pages with no reserved intro slot at all, simply every
 *     section that exists). Full "between every pair" trigger
 *     behavior. Used by free-page.php (home, ueber-uns, etc.) and
 *     as the SECOND call on listing/fixed-structure pages.
 *
 *   'intro' — ONLY the section at order_index = 0, if one exists.
 *     If none exists, renders a single, specially-labeled trigger
 *     ("Einleitung hinzufügen") instead — capped at exactly one
 *     section, never a "between pairs" trigger, since there's
 *     nothing to be between. Used as the FIRST call on listing/
 *     fixed-structure pages (events, participants, team, contact,
 *     archive) — the slot that sits above the hardcoded listing/
 *     fixed markup, which is never itself a row in the pages table.
 *
 * Usage in any view:
 *   $sectionsMode = 'intro'; // or omit for 'rest'
 *   require __DIR__ . '/../components/sections/render-sections.php';
 *
 * $sections and $isLoggedIn available from BaseController::render()
 * $org and $pageKey available from BaseController::render()
 *
 * Deliberately NOT using a helper function for the per-section
 * render step (title/text/theme/etc. extraction + require) — PHP's
 * `global` keyword only reaches the engine's TRUE top-level scope,
 * never "whatever called the current require() chain". Since this
 * file itself runs inside BaseController::render()'s method scope
 * (not true global scope), a global-based helper silently failed
 * to see ANY of these variables. Repeating the extraction block
 * inline, once per mode-branch, avoids that trap entirely and
 * matches how the rest of this codebase already works — no function
 * declarations inside view-partial files.
 */

$sectionFile  = __DIR__ . '/section.php';
$partialsDir  = __DIR__ . '/partials/';
$heroFile     = __DIR__ . '/../hero.php';
$triggerFile  = $partialsDir . '_add-section-trigger.php';

$sectionsMode = $sectionsMode ?? 'rest';

if ($sectionsMode === 'intro') {
    // At most ONE section, reserved at order_index = 0 — never a
    // "between pairs" trigger, since this slot has no siblings of
    // its own; it either exists or it doesn't.
    $introSection = null;
    foreach ($sections as $s) {
        if ((int) $s->order_index === 0) {
            $introSection = $s;
            break;
        }
    }

    if ($introSection) {
        $section = $introSection;

        if (($section->type ?? 'section') === 'hero') {
            require $heroFile;
        } else {
            $theme       = $section->theme        ?? 'light';
            $bgImage     = $section->bg_image     ?? null;
            $image       = $section->image        ?? null;
            $imageCredit = $section->image_credit ?? null;
            $imagePos    = $section->image_pos    ?? 'none';
            $layout      = $section->layout       ?? '50-50';
            $align       = $section->align        ?? 'left';
            $title       = $section->title        ?? null;
            $subtitle    = $section->subtitle     ?? null;
            $text        = $section->text         ?? null;
            $cta         = $section->cta          ?? null;
            $objectFit   = $section->object_fit   ?? 'cover';

            require $sectionFile;
        }
    } elseif ($isLoggedIn) {
        $afterIndex  = null;
        $beforeIndex = 0;
        $label       = 'Einleitung hinzufügen';
        require $triggerFile;
    }
} else {
    // The hero, if present, is a fixed-structure concept entirely
    // separate from this page's free sections — always rendered
    // first, unconditionally, with NO add-section trigger ever
    // placed before OR immediately after it (it's not part of the
    // reorderable sequence at all, regardless of whatever
    // order_index its row happens to carry).
    $hasHero = false;
    foreach ($sections as $s) {
        if (($s->type ?? 'section') === 'hero') {
            $hasHero = true;
            $section = $s;
            require $heroFile;
            break;
        }
    }

    // 'rest' — every TRUE section (never the hero) with
    // order_index >= 1. Pages with no reserved intro slot at all
    // simply never have an order_index=0 row, so this is
    // equivalent to "every section" for them.
    $restSections = array_values(array_filter($sections, function ($s) {
        return ($s->type ?? 'section') !== 'hero' && (int) $s->order_index >= 1;
    }));
    $count = count($restSections);

    foreach ($restSections as $i => $section) {
        // Suppress the very first trigger when a hero exists —
        // its position is physically identical to "right after the
        // hero", which the hero's own fixed-first rule forbids.
        if ($isLoggedIn && !($i === 0 && $hasHero)) {
            $afterIndex  = $i === 0 ? null : $restSections[$i - 1]->order_index;
            $beforeIndex = $section->order_index;
            $label       = null;
            require $triggerFile;
        }

        $theme       = $section->theme        ?? 'light';
        $bgImage     = $section->bg_image     ?? null;
        $image       = $section->image        ?? null;
        $imageCredit = $section->image_credit ?? null;
        $imagePos    = $section->image_pos    ?? 'none';
        $layout      = $section->layout       ?? '50-50';
        $align       = $section->align        ?? 'left';
        $title       = $section->title        ?? null;
        $subtitle    = $section->subtitle     ?? null;
        $text        = $section->text         ?? null;
        $cta         = $section->cta          ?? null;
        $objectFit   = $section->object_fit   ?? 'cover';

        // Whether this section can move up/down within the
        // reorderable sequence — never past the hero (it's not
        // part of this sequence at all), never past the start/end.
        $canMoveUp   = $i > 0;
        $canMoveDown = $i < $count - 1;

        require $sectionFile;
    }

    if ($isLoggedIn) {
        $afterIndex  = $count === 0 ? null : $restSections[$count - 1]->order_index;
        $beforeIndex = null;
        $label       = null;
        require $triggerFile;
    }
}
