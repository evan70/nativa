<?php $this->layout('controllers::layouts/app') ?>

<?php $this->section('content') ?>
    <section class="hero-section hero-section--fw" data-section="hero">
        <div class="hero-section__content">
            <h1 class="hero-section__title"><?= $this->e($title) ?></h1>
            <p class="hero-section__description"><?= $this->e($message) ?></p>
            <div class="hero-section__buttons">
                <a href="/blog" class="btn btn--lg">Back to Blog</a>
            </div>
        </div>
    </section>
<?php $this->endSection() ?>
