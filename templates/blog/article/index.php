<?php $this->layout('controllers::layouts/app') ?>

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
                        <div class="card__header">
                            <h3 class="card__title"><?= $this->e($article->title) ?></h3>
                            <p class="card__subtitle">Article #<?= $this->e($article->id) ?></p>
                        </div>
                        <div class="card__body">
                            <p><?= $this->e(substr($article->content, 0, 100)) ?>...</p>
                        </div>
                        <footer class="card__footer">
                            <a href="/blog/<?= $this->e($article->id) ?>" class="btn btn--secondary btn--sm">Read More</a>
                        </footer>
                    </article>
                <?php endforeach ?>
            </div>
            
            <div style="margin-top: 3rem; text-align: center;">
                <a href="/articles/new" class="btn btn--lg">Create New Article</a>
            </div>
        </div>
    </section>
<?php $this->endSection() ?>
