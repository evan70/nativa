<?php
$currentPage = 'portfolio';
$this->layout('layouts.app');

$stackTags = static function (string $stack): array {
    return array_values(array_filter(array_map('trim', explode(',', $stack))));
};

$itemTags = static function (string $tags): array {
    return array_values(array_filter(array_map('trim', explode(',', $tags))));
};
?>

<?php $this->section('content') ?>
    <section class="section">
        <div class="container">
            <header class="page-header">
                <p class="page-header__subtitle"><?= $this->e($eyebrow) ?></p>
                <h1 class="page-header__title"><?= $this->e($heading) ?></h1>
                <p class="page-header__subtitle"><?= $this->e($description) ?></p>
            </header>

            <!-- Filter bar -->
            <div class="filter-bar">
                <div class="filter-bar__group">
                    <span class="filter-bar__label">Category:</span>
                    <div class="filter-bar__pills">
                        <a href="/portfolio" class="pill <?= $activeCategory === '' && $activeTag === '' ? 'pill--active' : '' ?>">All</a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="/portfolio?category=<?= $this->e(urlencode($cat)) ?>"
                               class="pill <?= $activeCategory === $cat ? 'pill--active' : '' ?>"><?= $this->e($cat) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if (!empty($allTags)): ?>
                    <div class="filter-bar__group">
                        <span class="filter-bar__label">Tags:</span>
                        <div class="filter-bar__pills">
                            <?php foreach ($allTags as $t): ?>
                                <a href="/portfolio?tag=<?= $this->e(urlencode($t)) ?>"
                                   class="pill pill--tag <?= $activeTag === $t ? 'pill--active' : '' ?>">#<?= $this->e($t) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (empty($projects)): ?>
                <div class="empty-state">
                    <p>No projects match the selected filters.</p>
                    <a href="/portfolio" class="btn btn--secondary">Clear filters</a>
                </div>
            <?php else: ?>
                <div class="card-grid card-grid--cols-3">
                    <?php foreach ($projects as $project): ?>
                        <?php $tags = $stackTags($project->stack); ?>
                        <?php $ptags = $itemTags($project->tags); ?>
                        <article class="card card--interactive">
                            <img class="card__image"
                                 src="<?= $this->e($project->image) ?>"
                                 alt="<?= $this->e($project->title) ?> project screenshot"
                                 loading="lazy" decoding="async" width="400" height="250">
                            <div class="card__header">
                                <h2 class="card__title"><?= $this->e($project->title) ?></h2>
                                <p class="card__subtitle"><?= $this->e($project->subtitle) ?></p>
                            </div>
                            <div class="card__body">
                                <p><?= $this->e($project->description) ?></p>
                                <p class="card__meta">
                                    <span class="card__meta-item">
                                        <span class="card__meta-label">Role:</span> <?= $this->e($project->role) ?>
                                    </span>
                                    <span class="card__meta-item">
                                        <span class="card__meta-label">Year:</span> <?= $this->e($project->year) ?>
                                    </span>
                                    <span class="card__meta-item">
                                        <span class="card__meta-label">Category:</span>
                                        <a href="/portfolio?category=<?= $this->e(urlencode($project->category)) ?>" class="card__category-link"><?= $this->e($project->category) ?></a>
                                    </span>
                                </p>
                            </div>
                            <footer class="card__footer">
                                <div class="card__tags">
                                    <?php if (!empty($ptags)): ?>
                                        <?php foreach ($ptags as $pt): ?>
                                            <a href="/portfolio?tag=<?= $this->e(urlencode($pt)) ?>" class="tag tag--link">#<?= $this->e($pt) ?></a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <?php foreach ($tags as $tag): ?>
                                        <span class="tag tag--stack"><?= $this->e($tag) ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <a href="/portfolio/<?= $this->e($project->slug) ?>"
                                   class="btn btn--secondary btn--sm"
                                   aria-label="View details for <?= $this->e($project->title) ?>">View Details</a>
                            </footer>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php $this->endSection() ?>
