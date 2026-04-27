<?php
use App\View;
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Vanilla Cards Architecture - Fast, lightweight BEM component-based UI for Nativa CMS." />
    <title><?= $this->e($title ?? 'Nativa Vanilla Cards') ?></title>
    <link rel="preconnect" href="https://res.cloudinary.com">
    <link rel="icon" type="image/svg+xml" href="/mark/favicon.svg" />
    
    <!-- Core Styles -->
    <?= View::vite('core-css') ?>
    
    <!-- Page Specific Assets -->
    <?php foreach (View::$pageAssets as $asset): ?>
        <?= View::vite($asset, true) ?>
    <?php endforeach; ?>

    <!-- Scripts -->
    <?= View::vite('init') ?>
    <?= View::vite('core-app') ?>
    <?= View::vite('theme-switcher') ?>
</head>
<body class="home-page">

    <!-- NAVBAR SECTION -->
    <nav class="navbar" data-section="navbar">
        <div class="navbar__container container">
            <a href="/" class="navbar__brand">
                <svg class="navbar__logo" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2L2 22h20L12 2z"/>
                </svg>
                Nativa
            </a>
            
            <div class="navbar__menu">
                <a href="/" class="navbar__link <?= $_SERVER['REQUEST_URI'] === '/' ? 'navbar__link--active' : '' ?>">Home</a>
                <a href="/blog" class="navbar__link <?= str_starts_with($_SERVER['REQUEST_URI'], '/blog') ? 'navbar__link--active' : '' ?>">Blog</a>
                <a href="/admin" class="navbar__link">Admin</a>
            </div>

            <div class="navbar__actions">
                <button class="icon-btn theme-toggle navbar__theme-toggle" aria-label="Toggle Theme">
                    <!-- Sun Icon (shown in dark theme) -->
                    <svg class="theme-toggle__sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <!-- Moon Icon (shown in light theme) -->
                    <svg class="theme-toggle__moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
                <button class="icon-btn navbar__toggle" aria-label="Toggle Menu">
                    <svg class="navbar__toggle-icon--meatball" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="12" cy="5" r="2"></circle>
                        <circle cx="12" cy="12" r="2"></circle>
                        <circle cx="12" cy="19" r="2"></circle>
                    </svg>
                    <svg class="navbar__toggle-icon--close" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <!-- MAIN LANDMARK -->
    <main>
        <?= $this->yield('content') ?>
    </main>

    <!-- FOOTER SECTION -->
    <footer class="footer">
        <div class="footer__container">
            <div class="footer__grid">
                <div class="footer__brand">
                    <h3 class="footer__brand-name">Nativa</h3>
                    <p class="footer__description">
                        Building the next generation of web applications with vanilla performance and BEM architecture.
                    </p>
                </div>
                
                <div class="footer__column">
                    <h4 class="footer__title">Product</h4>
                    <ul class="footer__list">
                        <li><a href="#" class="footer__link">Features</a></li>
                        <li><a href="#" class="footer__link">Components</a></li>
                        <li><a href="#" class="footer__link">Pricing</a></li>
                    </ul>
                </div>

                <div class="footer__column">
                    <h4 class="footer__title">Resources</h4>
                    <ul class="footer__list">
                        <li><a href="#" class="footer__link">Documentation</a></li>
                        <li><a href="#" class="footer__link">API Reference</a></li>
                        <li><a href="/blog" class="footer__link">Blog</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer__bottom">
                <p class="footer__copyright">© 2026 Nativa. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
