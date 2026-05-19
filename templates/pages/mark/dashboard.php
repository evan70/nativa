<?php
$currentPage = 'mark';
$this->layout('layouts.mark');
?>

<?php $this->section('content') ?>

<!-- MARK HERO SECTION (compact, homepage-inspired) -->
<section class="mark-hero" data-section="mark-hero">
    <h1 class="mark-hero__title">Dashboard</h1>
    <p class="mark-hero__subtitle">Overview of your Mark admin</p>
</section>

<!-- STATS SECTION -->
<?php if (!empty($stats)): ?>
<section class="mark-stats">
    <div class="card-grid card-grid--cols-4">
        <article class="card card--stat mark-stat">
            <div class="card__body">
                <h2 class="mark-stat__value"><?= $this->e((string)($stats['users'] ?? 0)) ?></h2>
                <p class="mark-stat__label">Registered accounts</p>
            </div>
        </article>
        <article class="card card--stat mark-stat">
            <div class="card__body">
                <h2 class="mark-stat__value"><?= $this->e((string)($stats['articles'] ?? 0)) ?></h2>
                <p class="mark-stat__label">Articles</p>
            </div>
        </article>
        <article class="card card--stat mark-stat">
            <div class="card__body">
                <h2 class="mark-stat__value"><?= $this->e((string)($stats['portfolio'] ?? 0)) ?></h2>
                <p class="mark-stat__label">Portfolio items</p>
            </div>
        </article>
        <article class="card card--stat mark-stat">
            <div class="card__body">
                <h2 class="mark-stat__value"><?= $this->e((string)($stats['activeNow'] ?? 0)) ?></h2>
                <p class="mark-stat__label">Active now</p>
            </div>
        </article>
    </div>
</section>
<?php endif ?>

<!-- QUICK ACTIONS SECTION -->
<section class="mark-quick-actions">
    <header class="mark-quick-actions__header">
        <h2 class="mark-quick-actions__title">Quick Actions</h2>
    </header>
    <div class="mark-quick-actions__grid">
        <a href="/mark/articles/new" class="btn">Create Article</a>
        <a href="/mark/tags/new" class="btn btn--secondary">New Tag</a>
        <a href="/mark/articles" class="btn btn--outline">Manage Articles</a>
        <a href="/mark/tags" class="btn btn--outline">Manage Tags</a>
    </div>
</section>

<!-- RECENT ITEMS SECTION -->
<section>
    <div class="card-grid card-grid--cols-2">
        <!-- Recent Users -->
        <article class="card">
            <div class="card__header">
                <h3 class="card__title">Recent Users</h3>
            </div>
            <div class="card__body">
                <?php if (!empty($recentUsers)): ?>
                <ul class="mark-activity-list">
                    <?php foreach ($recentUsers as $user): ?>
                    <li class="mark-activity-list__item">
                        <div>
                            <p class="mark-activity-list__title"><?= $this->e($user['name'] ?? $user['email'] ?? 'Unknown') ?></p>
                            <p class="mark-activity-list__meta"><?= $this->e($user['email'] ?? '') ?></p>
                        </div>
                        <span class="mark-activity-list__time"><?= $this->e($user['createdAt'] ?? '') ?></span>
                    </li>
                    <?php endforeach ?>
                </ul>
                <?php else: ?>
                <p class="form-hint">No users registered yet.</p>
                <?php endif ?>
            </div>
        </article>

        <!-- Registered Sections -->
        <article class="card">
            <div class="card__header">
                <h3 class="card__title">Sections</h3>
            </div>
            <div class="card__body">
                <?php if (!empty($sections)): ?>
                <div class="mark-section-list">
                    <?php foreach ($sections as $section): ?>
                    <div class="mark-section-list__item">
                        <strong><?= $this->e($section->getLabel()) ?></strong>
                    </div>
                    <?php endforeach ?>
                </div>
                <?php else: ?>
                <p class="form-hint">No sections registered.</p>
                <?php endif ?>
            </div>
        </article>
    </div>
</section>

<?php $this->endSection() ?>
