<?php
$this->layout('layouts.app');
?>

<?php
$lcpBase = 'https://res.cloudinary.com/epithemic/image/upload';
$lcpId = 'v1773169416/blog/dae2d1fd9b13c89bb5b4a89280099d7a_hqfarh';
$lcpDesktop = $lcpBase . '/f_auto,q_auto:eco,w_1280/' . $lcpId;
$lcpMobile = $lcpBase . '/f_auto,q_auto:eco,w_480/' . $lcpId;
$lcpFallback = $lcpBase . '/f_auto,q_auto:eco,w_800/' . $lcpId;
?>

<?php $this->section('content') ?>
    <!-- HERO SECTION -->
    <section class="hero-section hero-section--fw" data-section="hero">
        <!-- LCP image - hero with responsive picture element -->
        <picture>
            <source media="(min-width: 769px)" srcset="<?= $lcpDesktop ?>" fetchpriority="high">
            <img class="hero-section__bg-img"
                 src="<?= $lcpMobile ?>"
                 alt="Hero background"
                 fetchpriority="high"
                 loading="eager"
                 decoding="async">
        </picture>
        <div class="hero-section__overlay" aria-hidden="true"></div>
        <div class="hero-section__content">
            <span class="hero-section__eyebrow"><?= $this->e($eyebrow) ?></span>
            <h1 class="hero-section__title"><?= $this->e($title) ?></h1>
            <p class="hero-section__description">
                <?= $this->e($message) ?>
            </p>
            <div class="hero-section__buttons">
                <a href="/articles" class="btn btn--lg">Visit Blog</a>
                <a href="https://github.com/marko-php" class="btn btn--outline btn--lg">GitHub</a>
            </div>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section class="features-section" data-section="features">
        <div class="container">
            <header class="features-section__header">
                <h2 class="features-section__title">Core Features</h2>
                <p class="features-section__subtitle">Explore what we've built</p>
            </header>

            <div class="card-grid card-grid--cols-3">
                <!-- Interactive Card -->
                <article class="card card--interactive">
                    <div class="card__header">
                        <h3 class="card__title">Lightning Fast</h3>
                        <p class="card__subtitle">Zero dependencies</p>
                    </div>
                    <div class="card__body">
                        <p>Our vanilla components are extremely small, resulting in better load times and overall performance.</p>
                    </div>
                    <footer class="card__footer">
                        <button class="btn btn--secondary btn--sm">Learn about Lightning Fast</button>
                    </footer>
                </article>

                <!-- Featured Card -->
                <article class="card card--featured">
                    <div class="card__header">
                        <h3 class="card__title">BEM Methodology</h3>
                        <p class="card__subtitle">Scalable CSS</p>
                    </div>
                    <div class="card__body">
                        <p>Strict naming conventions make our styling robust, reusable and independent of page structure.</p>
                    </div>
                    <footer class="card__footer">
                        <button class="btn btn--block">Use This</button>
                    </footer>
                </article>

                <!-- Loading Card State -->
                <article class="card card--loading">
                    <div class="card__header">
                        <h3 class="card__title">Lazy Loaded</h3>
                    </div>
                    <div class="card__body">
                        <p>Loading shimmer effect out of the box.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>
<?php $this->endSection() ?>
