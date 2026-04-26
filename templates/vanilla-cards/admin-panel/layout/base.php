<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Marko Admin Dashboard - Powered by Nativa Vanilla Cards" />
    <title><?= $this->e($pageTitle ?? 'Marko Admin | Dashboard') ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    
    <!-- Vanilla Cards Assets -->
    <link rel="stylesheet" href="/mark/core-css.css">
    <script type="module" src="/mark/core-app.js"></script>
    
    <!-- Init script (blocks rendering, prevents FOUC) -->
    <script>
        (function() {
            try {
                const storedTheme = localStorage.getItem('nativa-theme');
                const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const themeToSet = storedTheme ? storedTheme : (systemPrefersDark ? 'dark' : 'light');
                document.documentElement.dataset.theme = themeToSet;
            } catch (e) {
                console.warn('Could not initialize theme', e);
            }
        })();
    </script>

    <?= $this->yield('head') ?>
</head>
<body class="admin-page has-sidebar">

    <?= $this->include('admin-panel::partials/navbar', ['currentUser' => $currentUser ?? null]) ?>

    <?= $this->include('admin-panel::partials/sidebar', ['menuItems' => $menuItems ?? []]) ?>

    <!-- MAIN LANDMARK -->
    <main>
        <?= $this->include('admin-panel::partials/flash', ['flashMessages' => $flashMessages ?? []]) ?>
        <?= $this->yield('content') ?>
    </main>

    <!-- Theme Toggle and Sidebar Toggle -->
    <script>
        document.querySelector('.sidebar-toggle')?.addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('sidebar--open');
        });

        const themeToggle = document.querySelector('.theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const currentTheme = document.documentElement.dataset.theme;
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                document.documentElement.dataset.theme = newTheme;
                localStorage.setItem('nativa-theme', newTheme);
            });
        }
    </script>
    <?= $this->yield('scripts') ?>
</body>
</html>
