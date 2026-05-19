<?php
use App\View;
use App\PageLayout;
$page = $currentPage ?? 'mark';
$bodyClass = PageLayout::bodyClass($page);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Mark Admin - Powered by Nativa Vanilla Cards" />
    <title><?= $this->e($pageTitle ?? 'Mark Admin') ?></title>

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

    <!-- NAVBAR (shared partial) -->
    <header class="layout-mark__navbar navbar">
        <?= $this->include('partials/navbar', [
            'currentUser' => $currentUser ?? null,
            'menuItems' => $menuItems ?? [],
        ]) ?>
    </header>

    <!-- RIGHT DRAWER -->
    <?= $this->include('partials/mark-drawer', [
        'menuItems' => $menuItems ?? [],
        'activeSection' => $activeSection ?? '',
    ]) ?>

    <!-- MAIN CONTENT -->
    <main class="layout-mark__main">
        <div class="container">
            <?= $this->include('partials/flash', ['flashMessages' => $flashMessages ?? []]) ?>
            <?= $this->yield('content') ?>
        </div>
    </main>

    <?= $this->yield('scripts') ?>
</body>
</html>
