<?php $this->layout('app.layouts.app') ?>

<?php $this->section('content') ?>
    <section class="hero-section hero-section--fw" data-section="hero">
        <div class="hero-section__content">
            <span class="hero-section__eyebrow">Article</span>
            <h1 class="hero-section__title"><?= $this->e($article->title) ?></h1>
        </div>
    </section>

    <section class="features-section">
        <div class="container">
            <article class="card">
                <div class="card__body">
                    <div class="article-content" style="white-space: pre-wrap; line-height: 1.6;"><?= $this->e($article->content) ?></div>
                </div>
                <footer class="card__footer">
                    <a href="/blog" class="btn btn--secondary">Back to Blog</a>
                </footer>
            </article>
        </div>
    </section>
<?php $this->endSection() ?>
