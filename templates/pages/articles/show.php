<?php
$currentPage = 'articles';
$this->layout('layouts.app');

$formatDate = static function (?\DateTimeInterface $value): ?string {
    return $value?->format('j M Y');
};
?>

<?php $this->section('content') ?>
    <section class="hero-section hero-section--fw" data-section="hero">
        <div class="hero-section__content">
            <span class="hero-section__eyebrow">Article</span>
            <h1 class="hero-section__title"><?= $this->e($article->title) ?></h1>
            <?php if (!empty($article->excerpt)): ?>
                <p class="hero-section__description"><?= $this->e($article->excerpt) ?></p>
            <?php endif; ?>
            <?php if ($publishedAt = $formatDate($article->createdAt)): ?>
                <p class="card__subtitle"><?= $this->e($publishedAt) ?></p>
            <?php endif; ?>
        </div>
    </section>

    <section class="features-section">
        <div class="container">
            <article class="card card--large">
                <?php if (!empty($article->image)): ?>
                    <img class="card__image"
                         src="<?= $this->e($article->image) ?>"
                         alt="<?= $this->e($article->title) ?> cover image"
                         loading="lazy" width="1200" height="750">
                <?php endif; ?>

                <div class="card__body">
                    <p class="card__subtitle">Slug: <?= $this->e($article->slug) ?></p>
                    <p class="card__subtitle">Status: <?= $this->e(ucfirst($article->status)) ?></p>
                    <p class="card__subtitle">Published: <?= $this->e($article->published ? 'Yes' : 'No') ?></p>
                    <?php if (!empty($article->categoryId)): ?>
                        <p class="card__subtitle">Category: #<?= $this->e((string) $article->categoryId) ?></p>
                    <?php endif; ?>
                    <div><?= $article->content ?></div>
                </div>

                <footer class="card__footer">
                    <a href="/articles" class="btn btn--secondary">Back to Blog</a>
                </footer>
            </article>
        </div>
    </section>
<?php $this->endSection() ?>
