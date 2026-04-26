<?php $this->layout('controllers::layouts/app') ?>

<?php $this->section('content') ?>
    <h1><?= $this->e($title) ?></h1>
    <p><?= $this->e($message) ?></p>

    <ul>
        <?php foreach ($articles as $article): ?>
            <li>
                <a href="/blog/<?= $this->e($article->id) ?>"><?= $this->e($article->title) ?></a>
            </li>
        <?php endforeach ?>
    </ul>
<?php $this->endSection() ?>
