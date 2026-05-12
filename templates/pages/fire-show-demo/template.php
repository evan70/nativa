<?php
$this->layout('layouts.app');
?>

<?php $this->section('content') ?>

<!-- Cursor elements (need to be in HTML for JS to find) -->
<div class="fs-cursor" id="fs-cursor"></div>
<div class="fs-cursor-ring" id="fs-cursor-ring"></div>
<div class="fs-progress" id="fs-progress"></div>

<!-- Navigation - Fire Show Theme -->
<nav class="fs-navbar">
    <div class="fs-navbar__brand">Ohnivá Show</div>
    <ul class="fs-navbar__links">
        <li class="fs-navbar__item">
            <a href="#sluzby" class="fs-navbar__link">Služby</a>
        </li>
        <li class="fs-navbar__item">
            <a href="#filosofia" class="fs-navbar__link">Filozofia</a>
        </li>
        <li class="fs-navbar__item">
            <a href="#kontakt" class="fs-navbar__link">Kontakt</a>
        </li>
    </ul>
</nav>

<!-- HERO SECTION -->
<section class="hero-section" id="hero">
    <div class="fs-glow"></div>
    <div class="fs-parallax"></div>

    <div class="hero-section__content">
        <span class="fs-text-stroke fs-reveal-hl fs-d0">OHNIVÁ</span>
        <span class="fs-text-fire fs-reveal-hl fs-d1">SHOW</span>
        <span class="fs-text-stroke fs-reveal-hl fs-d2">DEMO</span>

        <p class="hero-section__description fs-reveal-hl fs-d3">
            Ukážka všetkých 12 interaktívnych efektov.<br>
            <strong>Fire Show Theme</strong> — moderný, plynulý, živý.
        </p>

        <a href="#sluzby" class="btn btn--lg fs-reveal-hl fs-d4">
            Pozrieť efekty <span style="margin-left: 0.5rem">↓</span>
        </a>
    </div>

    <!-- Marquee -->
    <div class="fs-marquee-wrap">
        <div class="fs-marquee-track">
            <span>Fireshow</span><span class="fs-sep"> — </span>
            <span>Žonglovanie</span><span class="fs-sep"> — </span>
            <span>Svetelná show</span><span class="fs-sep"> — </span>
            <span>Chodúle</span><span class="fs-sep"> — </span>
            <span>Šaškovanie</span><span class="fs-sep"> — </span>
            <span>Firemné akcie</span><span class="fs-sep"> — </span>
            <span>Svadby</span><span class="fs-sep"> — </span>
            <span>Festivaly</span><span class="fs-sep"> — </span>
            <span>Fireshow</span><span class="fs-sep"> — </span>
            <span>Žonglovanie</span><span class="fs-sep"> — </span>
            <span>Svetelná show</span><span class="fs-sep"> — </span>
            <span>Chodúle</span><span class="fs-sep"> — </span>
            <span>Šaškovanie</span><span class="fs-sep"> — </span>
            <span>Firemné akcie</span><span class="fs-sep"> — </span>
            <span>Svadby</span><span class="fs-sep"> — </span>
            <span>Festivaly</span><span class="fs-sep"> — </span>
        </div>
    </div>
</section>

<!-- SERVICES SECTION -->
<section id="sluzby" class="fs-reveal">
    <div class="fs-glow"></div>
    <div class="container">
        <div class="fs-label">Čo ponúkame</div>

        <div class="fs-service-list">
            <div class="fs-service-item">
                <div class="fs-service-fill"></div>
                <span class="fs-service-num">01</span>
                <span class="fs-service-name">Ohňová show</span>
                <span class="fs-service-sub">Poi · Tyče · Ventilátor</span>
            </div>
            <div class="fs-service-item">
                <div class="fs-service-fill"></div>
                <span class="fs-service-num">02</span>
                <span class="fs-service-name">Svetelná show</span>
                <span class="fs-service-sub">LED · Indoor · Outdoor</span>
            </div>
            <div class="fs-service-item">
                <div class="fs-service-fill"></div>
                <span class="fs-service-num">03</span>
                <span class="fs-service-name">Žonglovanie</span>
                <span class="fs-service-sub">Loptičky · Kužele · Klobúky</span>
            </div>
            <div class="fs-service-item">
                <div class="fs-service-fill"></div>
                <span class="fs-service-num">04</span>
                <span class="fs-service-name">Šaškovanie</span>
                <span class="fs-service-sub">Pouličné umenie · Interakcia</span>
            </div>
            <div class="fs-service-item">
                <div class="fs-service-fill"></div>
                <span class="fs-service-num">05</span>
                <span class="fs-service-name">Chodúle</span>
                <span class="fs-service-sub">Festivaly · Firemné akcie</span>
            </div>
        </div>
    </div>
</section>

<!-- PREČO MY - Normal Grid -->
<section id="proc" class="fs-reveal">
    <div class="fs-glow"></div>
    <div class="container">
        <div class="fs-label">Prečo my</div>
        
        <div class="card-grid card-grid--cols-3">
            <article class="card card--interactive">
                <div class="card__header">
                    <h3 class="card__title">Skúsenosti</h3>
                    <p class="card__subtitle">11 rokov</p>
                </div>
                <div class="card__body">
                    <p>Desiatky úspešných vystúpení na Slovensku aj v zahraničí.</p>
                </div>
            </article>
            
            <article class="card card--interactive">
                <div class="card__header">
                    <h3 class="card__title">Bezpečnosť</h3>
                    <p class="card__subtitle">Prvoradá</p>
                </div>
                <div class="card__body">
                    <p>Certifikované rekvizity a poistenie vystúpenia.</p>
                </div>
            </article>
            
            <article class="card card--interactive">
                <div class="card__header">
                    <h3 class="card__title">Individuálne</h3>
                    <p class="card__subtitle">Na mieru</p>
                </div>
                <div class="card__body">
                    <p>Každú akciu prispôsobime vašim potrebám.</p>
                </div>
            </article>
        </div>
    </div>
</section>

<!-- SCROLL TEXT FILL SECTION -->
<section id="filosofia" class="fs-reveal">
    <div class="fs-glow"></div>
    <div class="fs-label">Filozofia</div>

    <div style="margin-top: 5rem">
        <div class="fs-scroll-fill-wrap">
            <span class="fs-scroll-fill-base">OHEŇ</span>
            <span class="fs-scroll-fill-over">OHEŇ</span>
        </div>
        <div class="fs-scroll-fill-wrap">
            <span class="fs-scroll-fill-base">JE</span>
            <span class="fs-scroll-fill-over fs-b">JE</span>
        </div>
        <div class="fs-scroll-fill-wrap">
            <span class="fs-scroll-fill-base">REMESLO.</span>
            <span class="fs-scroll-fill-over fs-c">REMESLO.</span>
        </div>
    </div>
</section>

<!-- CONTACT SECTION -->
<section id="kontakt" class="fs-reveal">
    <div class="fs-glow"></div>
    <div class="fs-label">Spojte sa s nami</div>

    <h2 style="font-size: clamp(2rem, 6vw, 4rem); font-weight: 900; margin-bottom: 2rem;">
        Máte záujem<br>o spoluprácu?
    </h2>

    <a href="mailto:info@ohniva-show.sk" class="fs-email" style="font-size: clamp(1.5rem, 4vw, 3rem); color: var(--color-text); text-decoration: none;">
        info@ohniva-show.sk
    </a>

    <p style="margin-top: 3rem; color: var(--color-text-muted);">
        Pôsobíme po celom <strong>Slovensku</strong> a okolí.
    </p>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="footer__brand">Ohnivá Show Demo</div>
    <div>© 2025 Nativa</div>
    <div>Fire Show Theme Demo</div>
</footer>

<style>
/* Local styles for demo page */
.fs-label {
    font-family: var(--font-sans);
    font-size: 0.5rem;
    letter-spacing: 0.35em;
    text-transform: uppercase;
    color: var(--brand-ruby);
    margin-bottom: 3rem;
    display: flex;
    align-items: center;
    gap: 1.2rem;
}

.fs-label::before {
    content: '';
    width: 24px;
    height: 1px;
    background: var(--brand-ruby);
    flex-shrink: 0;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.hero-section__content {
    position: relative;
    z-index: 1;
    padding: 9rem 2rem;
    text-align: center;
}

.hero-section__description {
    font-size: 1.2rem;
    color: var(--color-text-muted);
    margin: 2rem 0;
    line-height: 1.75;
}

.hero-section__description strong {
    color: var(--brand-ruby);
}

.footer {
    padding: 2rem;
    text-align: center;
    border-top: 1px solid var(--color-border);
    font-family: var(--font-sans);
    font-size: 0.45rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--color-text-muted);
}

.footer__brand {
    color: var(--brand-ruby);
    margin-bottom: 0.5rem;
}
</style>

<?php $this->endSection() ?>