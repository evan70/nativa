<?php $this->layout('app.layouts.app') ?>

<?php
$formatDate = static function (?\DateTimeInterface $value): ?string {
    return $value?->format('j M Y');
};
?>

<?php $this->section('content') ?>
    <section class="hero-section hero-section--fw" data-section="hero">
        <div class="hero-section__content">
            <h1 class="hero-section__title"><?= $this->e($title) ?></h1>
            <p class="hero-section__description">
                <?= $this->e($message) ?>
            </p>
        </div>
    </section>

    <section class="features-section" data-section="features">
        <div class="container">
            <div class="card-grid card-grid--cols-3">
                <?php foreach ($articles as $article): ?>
                    <article class="card card--interactive">
                        <?php if (!empty($article->image)): ?>
                            <img class="card__image"
                                 src="<?= $this->e($article->image) ?>"
                                 alt="<?= $this->e($article->title) ?> cover image"
                                 loading="lazy" width="400" height="250">
                        <?php endif; ?>
                        <div class="card__header">
                            <h3 class="card__title"><?= $this->e($article->title) ?></h3>
                            <p class="card__subtitle"><?= $this->e($article->excerpt ?: $article->slug) ?></p>
                        </div>
                        <div class="card__body">
                            <?php
                            $meta = [];
                            if ($publishedAt = $formatDate($article->createdAt)) {
                                $meta[] = $publishedAt;
                            }
                            if (!empty($article->status)) {
                                $meta[] = ucfirst($article->status);
                            }
                            if (!empty($article->categoryId)) {
                                $meta[] = 'Category #' . $article->categoryId;
                            }
                            ?>
                            <?php if ($meta): ?>
                                <p class="card__subtitle"><?= $this->e(implode(' · ', $meta)) ?></p>
                            <?php endif; ?>

                            <p><?= $this->e(substr($article->content, 0, 140)) ?>...</p>
                        </div>
                        <footer class="card__footer">
                            <a href="/articles/<?= $this->e($article->slug) ?>" class="btn btn--secondary btn--sm">Read More</a>
                        </footer>
                    </article>
                <?php endforeach ?>
            </div>

            <div class="section section--sm">
                <article class="card">
                    <div class="card__body">
                        <h3 class="card__title">Create a new article</h3>
                        <p class="card__subtitle">Publish fresh content to the blog.</p>
                    </div>
                    <footer class="card__footer">
                        <a href="/mark/articles/new" class="btn btn--block">Create New Article</a>
                    </footer>
                </article>
            </div>
        </div>
    </section>
<?php $this->endSection() ?>
