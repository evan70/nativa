<?php
$this->layout('app.layouts.app');
?>

<?php $this->section('content') ?>
    <section class="section">
        <div class="container container--narrow">
            <header class="page-header">
                <p class="page-header__subtitle">404</p>
                <h1 class="page-header__title"><?= $this->e($heading ?? 'Page not found') ?></h1>
                <p class="page-header__subtitle"><?= $this->e($description ?? 'The page you are looking for does not exist.') ?></p>
            </header>

            <article class="card card--large">
                <div class="card__body">
                    <p class="card__subtitle">You can try one of these pages instead.</p>
                </div>
                <footer class="card__footer">
                    <a href="/" class="btn btn--secondary">Home</a>
                    <a href="/portfolio" class="btn btn--secondary">Portfolio</a>
                    <a href="/articles" class="btn btn--secondary">Blog</a>
                </footer>
            </article>
        </div>
    </section>
<?php $this->endSection() ?>
