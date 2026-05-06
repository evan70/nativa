<?php
use App\View;
$page = $currentPage ?? 'auth';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $this->e($pageTitle ?? 'Login') ?></title>
    <link rel="icon" type="image/svg+xml" href="/mark/favicon.svg" />

    <?php $origin = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? ''); ?>
    <link rel="preconnect" href="<?= $origin ?>" crossorigin>
    <link rel="preload" href="<?= View::resolve('assets/fonts/inter/Inter-Regular.woff2') ?>" as="font" type="font/woff2" crossorigin>

    <!-- Always loaded -->
    <?= View::vite('core') ?>
    <?= View::vite('init') ?>

    <!-- Page-specific -->
    <?= View::vite('page-' . $page) ?>

    <?= $this->yield('head') ?>
</head>
<body class="auth-page">
    <main class="auth-layout">
        <div class="auth-container">
            <?= $this->yield('content') ?>
        </div>
    </main>

    <?= $this->yield('scripts') ?>
</body>
</html>
