<?php
use App\View;
$page = $currentPage ?? 'dash';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Marko Admin Dashboard - Powered by Nativa Vanilla Cards" />
    <title><?= $this->e($pageTitle ?? 'Marko Admin | Dashboard') ?></title>
    <link rel="icon" type="image/svg+xml" href="/mark/favicon.svg" />

    <?php $origin = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? ''); ?>
    <link rel="preconnect" href="<?= $origin ?>" crossorigin>
    <link rel="preload" href="<?= View::resolve('assets/fonts/inter/Inter-Regular.woff2') ?>" as="font" type="font/woff2" crossorigin>

    <!-- Critical CSS inlined for first paint -->
    <style><?= View::criticalCss() ?></style>

    <!-- Always loaded -->
    <?= View::vite('init') ?>
    <?= View::vite('core') ?>

    <!-- Page-specific -->
    <?= View::vite('page-' . $page) ?>

    <?= $this->yield('head') ?>
</head>
<body class="admin-layout">

    <aside class="admin-layout__sidebar sidebar">
        <?= $this->include('cardboard::partials/sidebar', ['menuItems' => $menuItems ?? []]) ?>
    </aside>

    <header class="admin-layout__navbar navbar">
        <?= $this->include('cardboard::partials/navbar', ['currentUser' => $currentUser ?? null]) ?>
    </header>

    <main class="admin-layout__main">
        <?= $this->include('cardboard::partials/flash', ['flashMessages' => $flashMessages ?? []]) ?>
        <?= $this->yield('content') ?>
    </main>

    <?= $this->yield('scripts') ?>
</body>
</html>
