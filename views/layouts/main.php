<?php
/** @var string $content */
/** @var string|null $title */
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Vite &amp; Gourmand, traiteur à Bordeaux depuis 25 ans : menus de fêtes, repas de famille et évènements livrés chez vous.">
    <title><?= isset($title) ? e($title) . ' — ' : '' ?>Vite &amp; Gourmand, traiteur à Bordeaux</title>
    <link rel="icon" href="<?= asset('favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= asset('assets/vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
</head>
<body>
    <a class="skip-link" href="#contenu">Aller au contenu principal</a>

    <?php require BASE_PATH . '/views/partials/header.php'; ?>

    <main id="contenu" tabindex="-1">
        <?php require BASE_PATH . '/views/partials/flash.php'; ?>
        <?= $content ?>
    </main>

    <?php require BASE_PATH . '/views/partials/footer.php'; ?>

    <script src="<?= asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= asset('assets/js/app.js') ?>"></script>
    <?php foreach (\App\Core\View::scripts() as $script): ?>
        <script src="<?= asset(str_contains($script, '/') ? 'assets/' . $script : 'assets/js/' . $script) ?>"></script>
    <?php endforeach; ?>
</body>
</html>
