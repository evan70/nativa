<?php
$currentPage = 'articles';
$this->layout('layouts.app');

$formatDate = static function (?\DateTimeInterface $value): ?string {
    return $value?->format('j M Y');
};

// Use data from controller — searchQuery and tagSlug are passed in view data
$searchQuery = $this->e($searchQuery ?? '');
$tagSlug = $tagSlug ?? '';
$isSearch = $searchQuery !== '';
$categoryName_clean = $categoryName ?? '';
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
            <!-- Tag Cloud -->
            <?php if (!empty($allTags)): ?>
                <div class="tag-cloud">
                    <span class="tag-cloud__label">Tags:</span>
                    <div class="tag-cloud__items">
                        <?php foreach ($allTags as $tag):
                            $active = $tag['slug'] === $tagSlug;
                            $count = (int) ($tag['article_count'] ?? 0);
                            $href = $active ? '/articles' : '/articles?tag=' . $this->e($tag['slug']);
                            $cls = 'tag-cloud__item';
                            if ($active) $cls .= ' tag-cloud__item--active';
                            if ($count === 0) $cls .= ' tag-cloud__item--empty';
                        ?>
                            <a href="<?= $href ?>" class="<?= $cls ?>">
                                <?= $this->e($tag['name']) ?>
                                <span class="tag-cloud__count"><?= $count ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Category Cloud -->
            <?php if (!empty($allCategories)): ?>
                <div class="tag-cloud">
                    <span class="tag-cloud__label">Categories:</span>
                    <div class="tag-cloud__items">
                        <?php foreach ($allCategories as $cat):
                            $active = $cat['name'] === $categoryName_clean;
                            $count = (int) ($cat['article_count'] ?? 0);
                            $href = $active ? '/articles' : '/articles?category=' . $this->e(urlencode($cat['name']));
                            $cls = 'tag-cloud__item';
                            if ($active) $cls .= ' tag-cloud__item--active';
                            if ($count === 0) $cls .= ' tag-cloud__item--empty';
                        ?>
                            <a href="<?= $href ?>" class="<?= $cls ?>">
                                <?= $this->e($cat['name']) ?>
                                <span class="tag-cloud__count"><?= $count ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- FTS Search Form -->
            <form class="search-form" method="get" action="/articles" role="search">
                <div class="search-form__group">
                    <input
                        type="search"
                        name="q"
                        class="search-form__input"
                        placeholder="Search articles..."
                        value="<?= $searchQuery ?>"
                        aria-label="Search articles by full-text"
                    >
                    <button type="submit" class="btn btn--primary btn--sm search-form__submit">
                        Search
                    </button>
                    <?php if ($isSearch): ?>
                        <a href="/articles" class="btn btn--ghost btn--sm search-form__clear">Clear</a>
                    <?php endif; ?>
                </div>
                <?php if ($isSearch): ?>
                    <p class="search-form__results">
                        Search results for: <strong><?= $this->e($searchQuery) ?></strong>
                        <?php if (!empty($pagination['total'])): ?>
                            · <?= $pagination['total'] ?> article<?= $pagination['total'] !== 1 ? 's' : '' ?> found
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </form>

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
                            if (!empty($article->categoryName)) {
                                $meta[] = '<a href="/articles?category=' . $this->e(urlencode($article->categoryName)) . '" class="article-category">' . $this->e($article->categoryName) . '</a>';
                            } elseif (!empty($article->categoryId)) {
                                $meta[] = 'Category #' . $article->categoryId;
                            }
                            ?>
                            <?php if ($meta): ?>
                                <p class="card__subtitle"><?= implode(' · ', $meta) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($article->tags)): ?>
                                <div class="article-tags">
                                    <?php foreach ($article->tags as $tag): ?>
                                        <a href="/articles?tag=<?= $this->e($tag['slug']) ?>" class="article-tag">
                                            <?= $this->e($tag['name']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($isSearch && $article->snippet !== null): ?>
                                <p class="card__snippet"><?= $article->snippet ?></p>
                            <?php else: ?>
                                <p><?= $this->e(substr($article->content, 0, 140)) ?>...</p>
                            <?php endif; ?>
                        </div>
                        <footer class="card__footer">
                            <a href="/articles/<?= $this->e($article->slug) ?>"
                               class="btn btn--secondary btn--sm"
                               aria-label="Read more about <?= $this->e($article->title) ?>">Read More</a>
                        </footer>
                    </article>
                <?php endforeach ?>
            </div>

            <?php if (!empty($pagination['has_more'])):
                $nextPage = ($pagination['page'] ?? 1) + 1;
                $loadMoreUrl = '/articles/load?page=' . $nextPage;
                $loadSearch = $searchQuery !== '' ? urlencode($searchQuery) : '';
                if ($loadSearch !== '') {
                    $loadMoreUrl .= '&q=' . $loadSearch;
                }
                if ($tagSlug !== '') {
                    $loadMoreUrl .= '&tag=' . urlencode($tagSlug);
                }
                if ($categoryName_clean !== '') {
                    $loadMoreUrl .= '&category=' . urlencode($categoryName_clean);
                }
            ?>
                <div class="load-more-section" id="load-more-section">
                    <button
                        class="btn btn--secondary load-more-btn"
                        hx-get="<?= $loadMoreUrl ?>"
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
