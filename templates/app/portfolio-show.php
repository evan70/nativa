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

            <?php if ($project === null): ?>
                <article class="card card--large">
                    <div class="card__body">
                        <h2 class="card__title">Project not found</h2>
                        <p class="card__subtitle">The requested portfolio item does not exist.</p>
                    </div>
                    <footer class="card__footer">
                        <a href="/portfolio" class="btn btn--secondary">Back to Portfolio</a>
                    </footer>
                </article>
            <?php else: ?>
                <?php $tags = $stackTags($project->stack); ?>
                <article class="card card--large">
                    <img class="card__image"
                         src="<?= $this->e($project->image) ?>"
                         alt="<?= $this->e($project->title) ?> project screenshot"
                         loading="lazy" width="1200" height="750">
                    <div class="card__header">
                        <h2 class="card__title"><?= $this->e($project->title) ?></h2>
                        <p class="card__subtitle"><?= $this->e($project->subtitle) ?></p>
                    </div>
                    <div class="card__body">
                        <p><?= $this->e($project->description) ?></p>
                        <p class="card__subtitle">Role: <?= $this->e($project->role) ?></p>
                        <p class="card__subtitle">Year: <?= $this->e($project->year) ?></p>
                        <p class="card__subtitle">Category: <?= $this->e($project->category) ?></p>
                        <p class="card__subtitle">Stack: <?= $this->e($project->stack) ?></p>
                    </div>
                    <footer class="card__footer">
                        <div>
                            <?php foreach ($tags as $tag): ?>
                                <span class="tag"><?= $this->e($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <a href="/portfolio" class="btn btn--secondary">Back to Portfolio</a>
                    </footer>
                </article>
            <?php endif; ?>
        </div>
    </section>
<?php $this->endSection() ?>
