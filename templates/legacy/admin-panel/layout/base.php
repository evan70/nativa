<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->e($pageTitle ?? 'Marko Admin') ?></title>
    <?= $this->yield('head') ?>
</head>
<body class="admin-layout">
    <div class="admin-wrapper">
        <?= $this->include('admin-panel::partials/sidebar', ['menuItems' => $menuItems ?? []]) ?>

        <main class="admin-main">
            <?= $this->include('admin-panel::partials/flash', ['flashMessages' => $flashMessages ?? []]) ?>

            <?= $this->yield('content') ?>
        </main>
    </div>
</body>
</html>
