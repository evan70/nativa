<?php $this->layout('controllers::layouts/app') ?>

<?php $this->section('content') ?>
    <div class="stack">
        <h1><?= $this->e($title) ?></h1>
        <p><?= $this->e($message) ?></p>

        <form action="/blog" method="post" class="stack">
            <div class="field">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="field">
                <label for="content">Content</label>
                <textarea id="content" name="content" required></textarea>
            </div>

            <div class="actions">
                <button type="submit">Publish Article</button>
                <a href="/blog" class="button secondary">Cancel</a>
            </div>
        </form>
    </div>
<?php $this->endSection() ?>
