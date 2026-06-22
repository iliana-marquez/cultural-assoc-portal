<?php

/**
 * section.php
 *
 * Universal section component.
 * All layout options controlled via variables from render-sections.php
 *
 * Variables (set by render-sections.php):
 *   $theme       string      'light' | 'dark'
 *   $bgImage     string|null Cloudinary URL for background image
 *   $image       string|null Cloudinary URL for section image
 *   $imageCredit string|null Photographer credit
 *   $imagePos    string      'left' | 'right' | 'none'
 *   $layout      string      '50-50' | '75-25' | '25-75'
 *   $align       string      'left' | 'center' | 'right'
 *   $title       string|null
 *   $subtitle    string|null
 *   $text        string|null
 *   $cta         object|null { label, url }
 *   $partialsDir string      Absolute path to partials/ — set by render-sections.php
 */

$themeClass = ($theme ?? 'light') === 'dark' ? 'dark-segment' : 'light-segment';

$bgStyle = !empty($bgImage)
    ? 'style="background-image: url(\'' . htmlspecialchars($bgImage) . '\')"'
    : '';

$cols = match ($layout ?? '50-50') {
    '75-25'   => ['text' => 'col-12 col-md-8', 'image' => 'col-12 col-md-4'],
    '25-75'   => ['text' => 'col-12 col-md-4', 'image' => 'col-12 col-md-8'],
    '100-100' => ['text' => 'col-12',          'image' => 'col-12'],
    default   => ['text' => 'col-12 col-md-6', 'image' => 'col-12 col-md-6'],
};

$alignClass = match ($align ?? 'left') {
    'center' => 'text-center',
    'right'  => 'text-end',
    default  => '',
};

$hasImage     = !empty($image);
$isImageBlock = in_array($imagePos ?? 'none', ['left', 'right']);
$showImageCol = $isImageBlock && ($hasImage || $isLoggedIn);
$imageLeft    = ($imagePos ?? 'none') === 'left';

$objectFit = $section->object_fit ?? 'cover';

$ctaAlignClass = $hasImage
    ? ($imageLeft ? 'align-self-end' : 'align-self-start')
    : match ($align ?? 'left') {
        'center' => 'align-self-center',
        'right'  => 'align-self-end',
        default  => 'align-self-start',
    };
?>

<?php if ($isLoggedIn): ?>
    <div class="editable-block"
        data-section-id="<?= $section->id ?? '' ?>"
        data-order-index="<?= $section->order_index ?? '' ?>"
        data-save-url="/page/section/<?= $section->id ?? '' ?>/save">
    <?php endif; ?>

    <section class="segment <?= $themeClass ?>" <?= $bgStyle ?>>

        <?php if (!empty($bgImage)): ?>
            <div class="segment-overlay"></div>
        <?php endif; ?>

        <?php if ($isLoggedIn): require $partialsDir . '_controls.php';
        endif; ?>

        <div class="container">
            <div class="row align-items-center g-5">

                <?php
                // Image col — always in DOM when logged in (hidden if text block)
                // Position: left renders before content, right renders after
                $contentClass = $showImageCol ? $cols['text'] : 'col-12 ' . $alignClass;
                ?>

                <?php if ($imageLeft && $showImageCol): ?>
                    <div class="<?= $cols['image'] ?> section-image-col">
                        <?php require $partialsDir . '_image.php'; ?>
                    </div>
                <?php endif; ?>

                <div class="<?= $contentClass ?> section-text-col" id="section-content-col-<?= $section->id ?>">
                    <?php require $partialsDir . '_content.php'; ?>
                </div>

                <?php if (!$imageLeft && $showImageCol): ?>
                    <div class="<?= $cols['image'] ?> section-image-col">
                        <?php require $partialsDir . '_image.php'; ?>
                    </div>
                <?php elseif ($isLoggedIn): ?>
                    <div class="<?= $cols['image'] ?> section-image-col d-none">
                        <?php require $partialsDir . '_image.php'; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </section>
    <?php if ($isLoggedIn): ?>
    </div>
<?php endif; ?>