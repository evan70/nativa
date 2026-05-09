<?php
use App\View;
use App\PageLayout;
$page = $currentPage ?? PageLayout::detect($currentTemplate ?? 'home');
$bodyClass = PageLayout::bodyClass($page);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Vanilla Cards Architecture - Fast, lightweight BEM component-based UI for Nativa CMS." />
    <title><?= $this->e($title ?? 'Nativa Vanilla Cards') ?></title>
    <?php $origin = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? ''); ?>
    <link rel="preconnect" href="<?= $origin ?>" crossorigin>
    <link rel="preconnect" href="https://res.cloudinary.com" crossorigin>
    <?php if (View::$lcpImage): ?>
        <link rel="preload" as="image" href="<?= View::$lcpImage ?>" fetchpriority="high">
    <?php endif; ?>
    <link rel="icon" type="image/svg+xml" href="/dist/favicon.svg" />

    <!-- Init first (theme FOUC prevention), then CSS, then app JS -->
    <?= View::viteJs('init') ?>
    <?= View::viteCss('core') ?>
    <?= View::viteCss('page-' . $page) ?>
    <?= View::viteJs('core') ?>
    <?= View::viteJs('page-' . $page) ?>

    <?= $this->yield('head') ?>
</head>
<body class="<?= $bodyClass ?>">

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
                <a href="/portfolio" class="navbar__link <?= str_starts_with($_SERVER['REQUEST_URI'], '/portfolio') ? 'navbar__link--active' : '' ?>">Portfolio</a>
                <a href="/articles/" class="navbar__link <?= str_starts_with($_SERVER['REQUEST_URI'], '/articles/') ? 'navbar__link--active' : '' ?>">Blog</a>
                <a href="/mark" class="navbar__link">Mark</a>
            </div>

            <div class="navbar__actions">
                <button class="icon-btn navbar__dev-toggle" id="dev-theme-switcher" aria-label="Neon Theme">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 22h20L12 2z"/>
                    </svg>
                </button>
                <button class="icon-btn navbar__dev-toggle" id="fire-theme-switcher" aria-label="Fire Theme">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                    </svg>
                </button>
                <button class="icon-btn theme-toggle navbar__theme-toggle" aria-label="Toggle Theme">
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
    <footer class="section section--sm footer">
        <div class="container">
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
                        <li><a href="/articles/" class="footer__link">Blog</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer__bottom">
                <p class="footer__copyright">&copy; 2026 Nativa. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <?= $this->yield('scripts') ?>
</body>
</html>
