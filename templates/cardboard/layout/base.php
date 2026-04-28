<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Marko Admin Dashboard - Powered by Nativa Vanilla Cards" />
    <title><?= $this->e($pageTitle ?? 'Marko Admin | Dashboard') ?></title>
    <link rel="icon" type="image/svg+xml" href="/mark/favicon.svg" />
    
    <!-- Vanilla Cards Assets -->
    <link rel="stylesheet" href="/mark/core-css.css">
    <script src="/mark/init.js"></script>
    <script type="module" src="/mark/core-app.js"></script>
    <script defer src="/mark/theme-switcher.js"></script>

    <?= $this->yield('head') ?>
</head>
<body class="admin-layout has-sidebar">

    <?= $this->include('cardboard::partials/navbar', ['currentUser' => $currentUser ?? null]) ?>

    <?= $this->include('cardboard::partials/sidebar', ['menuItems' => $menuItems ?? []]) ?>

    <!-- MAIN LANDMARK -->
    <main class="admin-layout__main">
        <?= $this->include('cardboard::partials/flash', ['flashMessages' => $flashMessages ?? []]) ?>
        <?= $this->yield('content') ?>
    </main>

    <?= $this->yield('scripts') ?>
</body>
</html>
