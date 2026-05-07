<?php
$currentPage = 'portfolio';
$this->layout('app.layouts.app');

$stackTags = static function (string $stack): array {
    return array_values(array_filter(array_map('trim', explode(',', $stack))));
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

            <div class="card-grid card-grid--cols-3">
                <?php foreach ($projects as $project): ?>
                    <?php $tags = $stackTags($project->stack); ?>
                    <article class="card card--interactive">
                        <img class="card__image"
                             src="<?= $this->e($project->image) ?>"
                             alt="<?= $this->e($project->title) ?> project screenshot"
                             loading="lazy" width="400" height="250">
                        <div class="card__header">
                            <h2 class="card__title"><?= $this->e($project->title) ?></h2>
                            <p class="card__subtitle"><?= $this->e($project->subtitle) ?></p>
                        </div>
                        <div class="card__body">
                            <p><?= $this->e($project->description) ?></p>
                            <p class="card__subtitle">Role: <?= $this->e($project->role) ?></p>
                            <p class="card__subtitle">Year: <?= $this->e($project->year) ?></p>
                            <p class="card__subtitle">Category: <?= $this->e($project->category) ?></p>
                        </div>
                        <footer class="card__footer">
                            <div>
                                <?php foreach ($tags as $tag): ?>
                                    <span class="tag"><?= $this->e($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <a href="/portfolio/<?= $this->e($project->slug) ?>" class="btn btn--secondary btn--sm">View Details</a>
                        </footer>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php $this->endSection() ?>
