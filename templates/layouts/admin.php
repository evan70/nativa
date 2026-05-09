<?php
use App\View;
use App\PageLayout;
$page = $currentPage ?? 'dash';
$bodyClass = PageLayout::bodyClass($page);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Marko Admin Dashboard - Powered by Nativa Vanilla Cards" />
    <title><?= $this->e($pageTitle ?? 'Marko Admin | Dashboard') ?></title>
    <link rel="icon" type="image/svg+xml" href="/dist/favicon.svg" />

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
<body class="<?= $bodyClass ?>">

    <aside class="layout-admin__sidebar sidebar">
        <?= $this->include('partials/sidebar', ['menuItems' => $menuItems ?? []]) ?>
    </aside>

    <header class="layout-admin__navbar navbar">
        <?= $this->include('partials/navbar', ['currentUser' => $currentUser ?? null]) ?>
    </header>

    <main class="layout-admin__main">
        <?= $this->include('partials/flash', ['flashMessages' => $flashMessages ?? []]) ?>
        <?= $this->yield('content') ?>
    </main>

    <?= $this->yield('scripts') ?>
</body>
</html>
