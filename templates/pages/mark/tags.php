<?php
$currentPage = 'mark';
$this->layout('layouts.mark');

$tags = $data['tags'] ?? [];
?>

<?php $this->section('content') ?>
<header class="page-header">
    <h1 class="page-header__title"><?= $this->e($title ?? 'Tags') ?></h1>
    <p class="page-header__subtitle">Mark / Tags</p>
</header>

<div class="card">
    <div class="card__body">
        <div class="mark-tags-toolbar">
            <a href="/mark/tags/new" class="btn">Create New Tag</a>
        </div>
    </div>
</div>

<?php if (empty($tags)): ?>
    <div class="card">
        <div class="card__body">
            <p class="form-hint">No tags yet.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card__body">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Articles</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tags as $tag): ?>
                        <tr>
                            <td><?= $tag['id'] ?></td>
                            <td><strong><?= $this->e($tag['name'] ?? 'Untitled') ?></strong></td>
                            <td><code><?= $this->e($tag['slug'] ?? '-') ?></code></td>
                            <td><span class="tag-article-count"><?= (int) ($tag['article_count'] ?? 0) ?></span></td>
                            <td>
                                <div class="mark-tags-actions">
                                    <a href="/mark/tags/<?= $tag['id'] ?>/edit" class="btn btn--sm btn--secondary">Edit</a>
                                    <form method="POST" action="/mark/tags/<?= $tag['id'] ?>" class="mark-tags-delete-form" onsubmit="return confirm('Delete this tag?')">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="mark-tags-delete-btn">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?php $this->endSection() ?>
