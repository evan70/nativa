<?php
$currentPage = 'dash';
$this->layout('layouts.admin');
?>

<?php $this->section('content') ?>
<header class="page-header">
    <h1>Welcome to the admin panel.</h1>
    <p class="page-header__subtitle">Dashboard / Overview</p>
</header>

<div class="card-grid card-grid--cols-4" style="margin-bottom: var(--space-8);">
    <article class="card card--stat">
        <div class="card__body">
            <p style="font-size: var(--text-xs); color: var(--color-text-muted); text-transform: uppercase; font-weight: var(--font-bold);">Total Revenue</p>
            <h2 style="margin: var(--space-2) 0;">$45,231.89</h2>
            <p style="font-size: var(--text-xs); color: var(--brand-emerald);">+20.1% from last month</p>
        </div>
    </article>
    <article class="card card--stat">
        <div class="card__body">
            <p style="font-size: var(--text-xs); color: var(--color-text-muted); text-transform: uppercase; font-weight: var(--font-bold);">Subscriptions</p>
            <h2 style="margin: var(--space-2) 0;">+2,350</h2>
            <p style="font-size: var(--text-xs); color: var(--brand-emerald);">+180.1% from last month</p>
        </div>
    </article>
    <article class="card card--stat">
        <div class="card__body">
            <p style="font-size: var(--text-xs); color: var(--color-text-muted); text-transform: uppercase; font-weight: var(--font-bold);">Sales</p>
            <h2 style="margin: var(--space-2) 0;">+12,234</h2>
            <p style="font-size: var(--text-xs); color: var(--brand-emerald);">+19% from last month</p>
        </div>
    </article>
    <article class="card card--stat">
        <div class="card__body">
            <p style="font-size: var(--text-xs); color: var(--color-text-muted); text-transform: uppercase; font-weight: var(--font-bold);">Active Now</p>
            <h2 style="margin: var(--space-2) 0;">+573</h2>
            <p style="font-size: var(--text-xs); color: var(--brand-emerald);">+201 since last hour</p>
        </div>
    </article>
</div>

<!-- Revenue Chart -->
<div class="card card--chart" style="margin-bottom: var(--space-8);">
    <div class="card__header">
        <h3 class="card__title">Revenue Trend</h3>
    </div>
    <div class="card__body">
        <canvas id="revenue-chart"></canvas>
    </div>
</div>

<div class="card-grid card-grid--cols-2">
    <article class="card">
        <div class="card__header">
            <h3 class="card__title">Recent Activity</h3>
        </div>
        <div class="card__body">
            <ul style="list-style: none; padding: 0;">
                <li style="padding: var(--space-3) 0; border-bottom: 1px solid var(--color-border);">
                    <p style="font-size: var(--text-sm);"><strong>John Doe</strong> bought a <strong>Pro Plan</strong></p>
                    <p style="font-size: var(--text-xs); color: var(--color-text-muted);">2 minutes ago</p>
                </li>
                <li style="padding: var(--space-3) 0; border-bottom: 1px solid var(--color-border);">
                    <p style="font-size: var(--text-sm);"><strong>Jane Smith</strong> reported an issue</p>
                    <p style="font-size: var(--text-xs); color: var(--color-text-muted);">15 minutes ago</p>
                </li>
                <li style="padding: var(--space-3) 0; border-bottom: 1px solid var(--color-border);">
                    <p style="font-size: var(--text-sm);"><strong>New user</strong> registered</p>
                    <p style="font-size: var(--text-xs); color: var(--color-text-muted);">1 hour ago</p>
                </li>
                <li style="padding: var(--space-3) 0;">
                    <p style="font-size: var(--text-sm);">System update completed</p>
                    <p style="font-size: var(--text-xs); color: var(--color-text-muted);">3 hours ago</p>
                </li>
            </ul>
        </div>
    </article>

    <article class="card">
        <div class="card__header">
            <h3 class="card__title">Registered Sections</h3>
        </div>
        <div class="card__body">
            <?php if (!empty($sections)): ?>
                <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                    <?php foreach ($sections as $section): ?>
                        <div class="section-item" style="padding: var(--space-2); border: 1px solid var(--color-border); border-radius: var(--radius-md);">
                            <strong><?= $this->e($section->getLabel()) ?></strong>
                        </div>
                    <?php endforeach ?>
                </div>
            <?php else: ?>
                <p>No sections registered.</p>
            <?php endif ?>
        </div>
    </article>
</div>

<!-- Subscriptions Chart -->
<div class="card card--chart" style="margin-bottom: var(--space-8);">
    <div class="card__header">
        <h3 class="card__title">Subscriptions by Quarter</h3>
    </div>
    <div class="card__body">
        <canvas id="subscriptions-chart"></canvas>
    </div>
</div>

<!-- Sales Doughnut Chart -->
<div class="card card--chart" style="margin-bottom: var(--space-8);">
    <div class="card__header">
        <h3 class="card__title">Sales Distribution</h3>
    </div>
    <div class="card__body">
        <canvas id="sales-chart"></canvas>
    </div>
</div>
<?php $this->endSection() ?>
