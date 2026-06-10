<?php

/**
 * section.php
 *
 * Universal section component.
 * All layout options controlled via variables from JSON.
 *
 * Variables:
 *   $theme       string      'light' | 'dark'
 *   $bgImage     string|null URL for background image
 *   $image       string|null URL for section image
 *   $imageCredit string|null Photographer credit
 *   $imagePos    string      'left' | 'right' | 'none'
 *   $layout      string      '50-50' | '75-25' | '25-75'
 *   $align       string      'left' | 'center' | 'right' (text align, no-image only)
 *   $title       string|null
 *   $subtitle    string|null
 *   $text        string|null
 *   $cta         object|null { label, url }
 *   $objectFit   string      cover | contain 
 */

$themeClass = ($theme ?? 'light') === 'dark' ? 'dark-segment' : 'light-segment';

$bgStyle = !empty($bgImage)
    ? 'style="background-image: url(\'' . htmlspecialchars($bgImage) . '\')"'
    : '';

// Column sizes — only relevant when image present
$cols = match ($layout ?? '50-50') {
    '75-25' => ['text' => 'col-12 col-md-8', 'image' => 'col-12 col-md-4'],
    '25-75' => ['text' => 'col-12 col-md-4', 'image' => 'col-12 col-md-8'],
    default  => ['text' => 'col-12 col-md-6', 'image' => 'col-12 col-md-6'],
};

// Text alignment — only relevant when no image
$alignClass = match ($align ?? 'left') {
    'center' => 'text-center',
    'right'  => 'text-end',
    default  => '',
};

$hasImage    = !empty($image) && ($imagePos ?? 'none') !== 'none';
$imageLeft   = ($imagePos ?? 'none') === 'left';
$objectFit = $section->object_fit ?? 'cover';

$ctaAlignClass = $hasImage
    ? ($imageLeft ? 'align-self-end' : 'align-self-start')
    : match ($align ?? 'left') {
        'center' => 'align-self-center',
        'right'  => 'align-self-end',
        default  => 'align-self-start',
    };
?>

<section class="segment <?= $themeClass ?>" <?= $bgStyle ?>>

    <?php if (!empty($bgImage)): ?>
        <div class="segment-overlay"></div>
    <?php endif; ?>

    <?php if ($isLoggedIn): require $partialsDir . '_controls.php';
    endif; ?>

    <div class="container">
        <div class="row align-items-center g-5">

            <?php if ($hasImage && $imageLeft): ?>
                <div class="<?= $cols['image'] ?>">
                    <?php require $partialsDir . '_image.php'; ?>
                </div>
            <?php endif; ?>

            <div class="<?= $hasImage ? $cols['text'] : 'col-12 ' . $alignClass ?>">
                <?php require $partialsDir . '_content.php'; ?>
            </div>

            <?php if ($hasImage && !$imageLeft): ?>
                <div class="<?= $cols['image'] ?>">
                    <?php require $partialsDir . '_image.php'; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>

</section>