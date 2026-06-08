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

    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/main.css">
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- Navbar -->
    <?php require __DIR__ . '/../components/nav.php'; ?>

    <!-- Edit-Bar for Editing Mode only -->
    <?php if ($isLoggedIn): ?>
        <?php require __DIR__ . '/../components/edit-bar.php'; ?>
    <?php endif; ?>

    <!-- Main content (Mask) -->
    <main>
        <?= $content ?>
    </main>

    <!-- Footer -->
    <?php require __DIR__ . '/../components/footer.php'; ?>

    <!-- Bootstrap JS Script-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <!-- Script -->
    <script src="/assets/js/app.js"></script>
</body>

</html>