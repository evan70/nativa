<?php
use App\View;
$page = $currentPage ?? 'auth';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $this->e($pageTitle ?? 'Login') ?></title>
    <link rel="icon" type="image/svg+xml" href="/mark/favicon.svg" />

    <?php $origin = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? ''); ?>
    <link rel="preconnect" href="<?= $origin ?>" crossorigin>
    <!-- Init first (theme FOUC prevention), then CSS, then app JS -->
    <?= View::viteJs('init') ?>
    <?= View::viteCss('core') ?>
    <?= View::viteCss('page-' . $page) ?>
    <?= View::viteJs('core') ?>
    <?= View::viteJs('page-' . $page) ?>

    <?= $this->yield('head') ?>
</head>
<body class="page-auth">
    <main>
        <div class="container container--narrow">
            <?= $this->yield('content') ?>
        </div>
    </main>

    <?= $this->yield('scripts') ?>
</body>
</html>
