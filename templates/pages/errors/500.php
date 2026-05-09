<?php
$currentPage = 'errors';
$this->layout('layouts.app');
?>

<?php $this->section('content') ?>
    <section class="section">
        <div class="container container--narrow">
            <header class="page-header">
                <p class="page-header__subtitle">500</p>
                <h1 class="page-header__title"><?= $this->e($heading ?? 'Server Error') ?></h1>
                <p class="page-header__subtitle"><?= $this->e($description ?? 'Something went wrong on our end. Please try again later.') ?></p>
            </header>

            <article class="card card--large">
                <div class="card__body">
                    <p class="card__subtitle">We're working on fixing this. In the meantime, you can try one of these pages.</p>
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
