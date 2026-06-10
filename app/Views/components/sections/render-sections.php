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

foreach ($sections as $section):

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

    require $sectionFile;

endforeach;
