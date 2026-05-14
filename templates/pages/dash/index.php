<?php
declare(strict_types=1);
$currentPage = 'dash';
$this->layout('layouts.admin');
?>

<?php $this->section('content') ?>
<header class="page-header">
    <h1 class="page-header__title">Dashboard</h1>
    <p class="page-header__subtitle">Overview</p>
</header>

<section class="mb-8">
    <div class="card-grid card-grid--cols-4">
        <article class="card card--stat">
            <div class="card__body">
                <p class="card__subtitle">Users</p>
                <h2 class="card__title"><?= $this->e((string)($stats['users'] ?? 0)) ?></h2>
                <p class="stat-label">Registered accounts</p>
            </div>
        </article>
        <article class="card card--stat">
            <div class="card__body">
                <p class="card__subtitle">Articles</p>
                <h2 class="card__title"><?= $this->e((string)($stats['articles'] ?? 0)) ?></h2>
                <p class="stat-label">Published and drafts</p>
            </div>
        </article>
        <article class="card card--stat">
            <div class="card__body">
                <p class="card__subtitle">Portfolio</p>
                <h2 class="card__title"><?= $this->e((string)($stats['portfolio'] ?? 0)) ?></h2>
                <p class="stat-label">Portfolio items</p>
            </div>
        </article>
        <article class="card card--stat">
            <div class="card__body">
                <p class="card__subtitle">Active Now</p>
                <h2 class="card__title"><?= $this->e((string)($stats['activeNow'] ?? 0)) ?></h2>
                <p class="stat-label">Users online</p>
            </div>
        </article>
    </div>
</section>

<section class="mb-8">
    <div class="card-grid card-grid--cols-2">
        <article class="card">
            <div class="card__header">
                <h3 class="card__title">Recent Users</h3>
            </div>
            <div class="card__body">
                <?php if (!empty($recentUsers)): ?>
                <ul class="activity-list">
                    <?php foreach ($recentUsers as $user): ?>
                    <li class="activity-list__item">
                        <div class="activity-list__content">
                            <p class="activity-list__title"><?= $this->e($user['name'] ?? $user['email'] ?? 'Unknown') ?></p>
                            <p class="activity-list__meta"><?= $this->e($user['email'] ?? '') ?></p>
                        </div>
                        <span class="activity-list__time"><?= $this->e($user['createdAt'] ?? '') ?></span>
                    </li>
                    <?php endforeach ?>
                </ul>
                <?php else: ?>
                <p class="form-hint">No users registered yet.</p>
                <?php endif ?>
            </div>
        </article>

        <article class="card">
            <div class="card__header">
                <h3 class="card__title">Registered Sections</h3>
            </div>
            <div class="card__body">
                <?php if (!empty($sections)): ?>
                <div class="section-list">
                    <?php foreach ($sections as $section): ?>
                    <div class="section-list__item">
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
