<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($seo['title'] ?? 'Klubalsergrund') ?></title>
    <meta name="description" content="<?= htmlspecialchars($seo['description'] ?? '') ?>">

    <link rel="canonical" href="<?= htmlspecialchars($seo['url'] ?? '') ?>">
    <!-- <link rel="icon" href=" dynamic "> -->


    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($seo['title'] ?? '') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seo['description'] ?? '') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($seo['image'] ?? '') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($seo['url'] ?? '') ?>">
    <meta property="og:type" content="<?= htmlspecialchars($seo['type'] ?? 'website') ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($seo['title'] ?? '') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($seo['description'] ?? '') ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($seo['image'] ?? '') ?>">

    <!-- JSON-LD Schema -->
    <?= $seo['schema'] ?? '' ?>

</head>

<body>
    <?php require __DIR__ . '/../components/nav.php'; ?>

    <main>
        <?= $content ?>
    </main>

    <?php require __DIR__ . '/../components/footer.php'; ?>
</body>

</html>