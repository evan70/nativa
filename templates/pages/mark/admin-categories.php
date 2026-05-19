<?php
$currentPage = 'mark';
$this->layout('layouts.mark');

$categories = $data['categories'] ?? [];
?>

<?php $this->section('content') ?>
<header class="page-header">
    <h1 class="page-header__title"><?= $this->e($title ?? 'Categories') ?></h1>
    <p class="page-header__subtitle">Mark / Categories</p>
</header>

<div class="card">
    <div class="card__body">
        <div class="mark-tags-toolbar">
            <a href="/mark/categories/new" class="btn">Create New Category</a>
        </div>
    </div>
</div>

<?php if (empty($categories)): ?>
    <div class="card">
        <div class="card__body">
            <p class="form-hint">No categories yet.</p>
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
                        <th>Description</th>
                        <th>Articles</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= $category['id'] ?></td>
                            <td><strong><?= $this->e($category['name'] ?? 'Untitled') ?></strong></td>
                            <td><code><?= $this->e($category['slug'] ?? '-') ?></code></td>
                            <td><?= $this->e($category['description'] ?? '') ?></td>
                            <td><span class="tag-article-count"><?= (int) ($category['article_count'] ?? 0) ?></span></td>
                            <td>
                                <div class="mark-tags-actions">
                                    <a href="/mark/categories/<?= $category['id'] ?>/edit" class="btn btn--sm btn--secondary">Edit</a>
                                    <form method="POST" action="/mark/categories/<?= $category['id'] ?>" class="mark-tags-delete-form" onsubmit="return confirm('Delete this category?')">
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
