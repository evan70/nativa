<?php
$currentPage = 'articles';
$this->layout('layouts.admin');

$articles = $data['articles'] ?? [];
?>

<?php $this->section('content') ?>
<header class="page-header">
    <h1><?= $this->e($title ?? 'Articles Administration') ?></h1>
    <p class="page-header__subtitle">Dashboard / Articles</p>
</header>

<div class="card" style="margin-bottom: var(--space-4);">
    <div class="card__body" style="display: flex; justify-content: flex-end;">
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
        <div class="card__body" style="padding: 0;">
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
                                <a href="/articles/<?= $this->e($article->slug ?? $article->id) ?>" class="link" style="font-weight: var(--font-bold);">
                                    <?= $this->e($article->title ?? 'Untitled') ?>
                                </a>
                            </td>
                            <td>
                                <span style="font-size: var(--text-xs); font-weight: var(--font-bold); text-transform: uppercase; color: <?= $article->status === 'published' ? 'var(--color-success)' : 'var(--color-text-muted)' ?>;">
                                    <?= $this->e($article->status ?? 'draft') ?>
                                </span>
                            </td>
                            <td><?= $article->published ? 'Yes' : 'No' ?></td>
                            <td><?= $article->createdAt?->format('Y-m-d') ?? '-' ?></td>
                            <td>
                                <div style="display: flex; gap: var(--space-2);">
                                    <a href="/mark/articles/<?= $article->id ?>/edit" class="btn btn--sm btn--secondary">Edit</a>
                                    <form method="POST" action="/mark/articles/<?= $article->id ?>" style="display:inline" onsubmit="return confirm('Delete this article?')">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn--sm" style="background-color: var(--color-error);">Delete</button>
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
