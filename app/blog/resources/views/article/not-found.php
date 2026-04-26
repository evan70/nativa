<?php $this->layout('controllers::layouts/app') ?>

<?php $this->section('content') ?>
    <div class="stack">
        <h1><?= $this->e($title) ?></h1>
        <p><?= $this->e($message) ?></p>
        <div class="actions">
            <a href="/blog" class="button">Back to Blog</a>
        </div>
    </div>
<?php $this->endSection() ?>
