<?php

/**
 * render-sections.php
 *
 * Reads sections array and renders each via section.php.
 *
 * Usage in any view:
 *   require __DIR__ . '/../components/sections/render-sections.php';
 *
 * $sections must be defined before requiring this file.
 * $isLoggedIn available from BaseController::render()
 */

// Absolute path — works regardless of where this file is required from
$sectionFile  = __DIR__ . '/section.php';
$partialsDir  = __DIR__ . '/partials/';
$heroFile = __DIR__ . '/../hero.php';

foreach ($sections as $section):


    // Hero section — pulls from $org, no JSON content needed
    if (($section->type ?? 'section') === 'hero') {
        require $heroFile;
        continue;
    }

    // Free section — extract variables from JSON content
    $theme       = $section->theme        ?? 'light';
    $bgImage     = $section->bg_image     ?? null;
    $image       = $section->image        ?? null;
    $objectFit   = $section->object_fit   ?? 'cover';
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

endforeach;
