<?php
$currentPage = 'articles';
$this->layout('layouts.admin');

$articles = $data['articles'] ?? [];
?>

<?php $this->section('content') ?>
<header class="page-header">
    <h1 class="page-header__title"><?= $this->e($title ?? 'Articles Administration') ?></h1>
    <p class="page-header__subtitle">Dashboard / Articles</p>
</header>

<div class="card article-toolbar-wrap">
    <div class="card__body article-toolbar">
        <a href="/mark/articles/new" class="btn">Create New Article</a>
    </div>
</div>

<?php if (empty($articles)): ?>
    <div class="card">
        <div class="card__body">
            <p class="form-hint">No articles yet.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card__body article-table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Published</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <td><?= $article->id ?></td>
                            <td>
                                <a href="/articles/<?= $this->e($article->slug ?? $article->id) ?>" class="link article-link">
                                    <?= $this->e($article->title ?? 'Untitled') ?>
                                </a>
                            </td>
                            <td>
                                <span class="article-status article-status--<?= $this->e($article->status ?? 'draft') ?>">
                                    <?= $this->e($article->status ?? 'draft') ?>
                                </span>
                            </td>
                            <td><?= $article->published ? 'Yes' : 'No' ?></td>
                            <td><?= $article->createdAt?->format('Y-m-d') ?? '-' ?></td>
                            <td>
                                <div class="article-actions">
                                    <a href="/mark/articles/<?= $article->id ?>/edit" class="btn btn--sm btn--secondary">Edit</a>
                                    <form method="POST" action="/mark/articles/<?= $article->id ?>" class="article-delete-form" onsubmit="return confirm('Delete this article?')">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="article-delete-btn">Delete</button>
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