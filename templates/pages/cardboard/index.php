<?php
$currentPage = 'cardboard';
$this->layout('layouts.default');
?>

<?php $this->section('content') ?>
<header class="page-header">
    <h1 class="page-header__title">Cardboard Components</h1>
    <p class="page-header__subtitle">Preview of cardboard sections</p>
</header>

<!-- Cardboard Graph Section -->
<section data-section="cardboard-graph" class="mb-16">
    <div class="container">
        <h2 class="cardboard-graph__title">Analytics Overview</h2>
        <p class="cardboard-graph__description">Monitor your key metrics over time</p>
        <div class="cardboard-graph__chart-container">
            <canvas class="cardboard-graph__chart" id="analyticsChart"></canvas>
        </div>
        <div class="cardboard-graph__controls">
            <button class="cardboard-graph__button cardboard-graph__button--active">Weekly</button>
            <button class="cardboard-graph__button">Monthly</button>
            <button class="cardboard-graph__button">Yearly</button>
            <button class="cardboard-graph__button">Custom</button>
        </div>
    </div>
</section>

<!-- Cardboard Quick Section -->
<section data-section="cardboard-quick">
    <div class="container">
        <h2 class="cardboard-quick__title">Quick Stats</h2>
        <p class="cardboard-quick__description">At-a-glance overview of your performance</p>
        <div class="cardboard-quick__grid">
            <div class="cardboard-quick__item cardboard-quick__animate">
                <div class="cardboard-quick__icon">📊</div>
                <div class="cardboard-quick__number">12,450</div>
                <div class="cardboard-quick__label">Page Views</div>
            </div>
            <div class="cardboard-quick__item cardboard-quick__animate">
                <div class="cardboard-quick__icon">👥</div>
                <div class="cardboard-quick__number">3,240</div>
                <div class="cardboard-quick__label">Unique Visitors</div>
            </div>
            <div class="cardboard-quick__item cardboard-quick__animate">
                <div class="cardboard-quick__icon">💰</div>
                <div class="cardboard-quick__number">$89,240</div>
                <div class="cardboard-quick__label">Revenue</div>
            </div>
            <div class="cardboard-quick__item cardboard-quick__animate">
                <div class="cardboard-quick__icon">⭐</div>
                <div class="cardboard-quick__number">4.8</div>
                <div class="cardboard-quick__label">Avg. Rating</div>
            </div>
            <div class="cardboard-quick__item cardboard-quick__animate">
                <div class="cardboard-quick__icon">📈</div>
                <div class="cardboard-quick__number">15%</div>
                <div class="cardboard-quick__label">Growth</div>
            </div>
            <div class="cardboard-quick__item cardboard-quick__animate">
                <div class="cardboard-quick__icon">⏱️</div>
                <div class="cardboard-quick__number">4.2s</div>
                <div class="cardboard-quick__label">Load Time</div>
            </div>
        </div>
    </div>
</section>
<?php $this->endSection() ?>