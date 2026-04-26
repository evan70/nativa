<?php $this->layout('controllers::layouts/app') ?>

<?php $this->section('content') ?>
    <article class="stack">
        <header>
            <span class="eyebrow">Article</span>
            <h1><?= $this->e($article->title) ?></h1>
        </header>

        <div class="article-content"><?= $this->e($article->content) ?></div>

        <footer>
            <a href="/blog" class="button secondary">Back to Blog</a>
        </footer>
    </article>
<?php $this->endSection() ?>
