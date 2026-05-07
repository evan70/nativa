<?php
use App\View;
$page = $currentPage ?? 'dash';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Marko Admin Dashboard - Powered by Nativa Vanilla Cards" />
    <title><?= $this->e($pageTitle ?? 'Marko Admin | Dashboard') ?></title>
    <link rel="icon" type="image/svg+xml" href="/mark/favicon.svg" />

    <?php $origin = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? ''); ?>
    <link rel="preconnect" href="<?= $origin ?>" crossorigin>
    <!-- Init first (theme), then CSS, then app JS -->
    <?= View::viteJs('init') ?>
    <?= View::viteCss('core') ?>
    <?= View::viteCss('page-' . $page) ?>
    <?= View::viteJs('core') ?>
    <?= View::viteJs('page-' . $page) ?>

    <?= $this->yield('head') ?>
</head>
<body class="layout-admin">

    <aside class="layout-admin__sidebar sidebar">
        <?= $this->include('cardboard::partials/sidebar', ['menuItems' => $menuItems ?? []]) ?>
    </aside>

    <header class="layout-admin__navbar navbar">
        <?= $this->include('cardboard::partials/navbar', ['currentUser' => $currentUser ?? null]) ?>
    </header>

    <main class="layout-admin__main">
        <?= $this->include('cardboard::partials/flash', ['flashMessages' => $flashMessages ?? []]) ?>
        <?= $this->yield('content') ?>
    </main>

    <?= $this->yield('scripts') ?>
</body>
</html>
