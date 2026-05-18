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
            <h1 class="hero-section__title"><?= $this->e($title) ?></h1>
            <p class="hero-section__description">
                <?= $this->e($message) ?>
            </p>
        </div>
    </section>

    <section class="features-section" data-section="features">
        <div class="container">
            <div class="card-grid card-grid--cols-3" id="article-list">
                <?php foreach ($articles as $article): ?>
                    <article class="card card--interactive">
                        <?php if (!empty($article->image)): ?>
                            <img class="card__image"
                                 src="<?= $this->e($article->image) ?>"
                                 alt="<?= $this->e($article->title) ?> cover image"
                                 loading="lazy" width="400" height="250">
                        <?php endif; ?>
                        <div class="card__header">
                            <h2 class="card__title"><?= $this->e($article->title) ?></h2>
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
                            <a href="/articles/<?= $this->e($article->slug) ?>" 
                               class="btn btn--secondary btn--sm"
                               aria-label="Read more about <?= $this->e($article->title) ?>">Read More</a>
                        </footer>
                    </article>
                <?php endforeach ?>
            </div>

            <?php if (!empty($pagination['has_more'])): ?>
                <div class="load-more-section" id="load-more-section">
                    <button
                        class="btn btn--secondary load-more-btn"
                        hx-get="/articles/load?page=2"
                        hx-target="#article-list"
                        hx-swap="beforeend"
                        hx-trigger="click"
                        hx-indicator="#load-more-spinner"
                    >
                        <span class="btn__text">Load More Articles</span>
                        <span id="load-more-spinner" class="htmx-indicator" aria-hidden="true">
                            <svg class="spinner" viewBox="0 0 24 24" width="20" height="20">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4 31.4">
                                    <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/>
                                </circle>
                            </svg>
                        </span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="section section--sm">
                <article class="card">
                    <div class="card__body">
                        <h2 class="card__title">Create a new article</h2>
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
