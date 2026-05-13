<?php

declare(strict_types=1);

use App\Blog\Entity\Article;

$articles = $data['articles'] ?? [];

// Escape helper function
if (!function_exists('h')) {
    function h(mixed $value): string {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
?>

<div class="admin-container">
    <h1><?= h($title ?? 'Articles Administration') ?></h1>

    <div class="admin-header">
        <a href="/mark/articles/new" class="btn btn--primary">Create New Article</a>
    </div>

    <?php if (empty($articles)): ?>
        <p class="empty-state">No articles yet.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Slug</th>
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
                            <a href="/articles/<?= h($article->slug ?? $article->id) ?>">
                                <?= h($article->title ?? 'Untitled') ?>
                            </a>
                        </td>
                        <td><?= h($article->slug ?? '-') ?></td>
                        <td><span class="badge badge--<?= $article->status ?? 'draft' ?>"><?= h($article->status ?? 'draft') ?></span></td>
                        <td><?= $article->published ? 'Yes' : 'No' ?></td>
                        <td><?= $article->createdAt?->format('Y-m-d') ?? '-' ?></td>
                        <td class="actions">
                            <a href="/mark/articles/<?= $article->id ?>/edit" class="btn btn--small btn--secondary">Edit</a>
                            <form method="POST" action="/mark/articles/<?= $article->id ?>" style="display:inline" onsubmit="return confirm('Delete this article?')">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn--small btn--danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>