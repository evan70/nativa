<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Marko Admin - Authentication" />
    <title><?= $this->e($pageTitle ?? 'Marko Admin | Authentication') ?></title>
    <link rel="icon" type="image/svg+xml" href="/mark/favicon.svg" />
    
    <!-- Auth-specific Assets -->
    <link rel="stylesheet" href="/mark/auth-style-3PjjQolB.css">
    <script src="/mark/init-B_U-wsDj.js"></script>
    <script type="module" src="/mark/auth-app-Cak4LoAm.js"></script>

    <?= $this->yield('head') ?>
</head>
<body class="auth-layout">

    <div class="auth-container">
        <?= $this->include('cardboard::partials/flash', ['flashMessages' => $flashMessages ?? []]) ?>
        <?= $this->yield('content') ?>
    </div>

    <?= $this->yield('scripts') ?>
</body>
</html>
