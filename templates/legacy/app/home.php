<?php $this->layout('controllers::layouts/app') ?>

<?php $this->section('content') ?>
    <div class="stack">
        <span class="eyebrow"><?= $this->e($eyebrow) ?></span>
        <h1><?= $this->e($title) ?></h1>
        <p><?= $this->e($message) ?></p>

        <div class="actions">
            <a href="/blog" class="button">Visit Blog</a>
            <a href="https://github.com/marko-php" class="button secondary">GitHub</a>
        </div>
    </div>
<?php $this->endSection() ?>
