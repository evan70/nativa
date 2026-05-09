<?php

declare(strict_types=1);

use App\Blog\Entity\Article;

$articles = $data['articles'] ?? [];
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
                    <th>Created</th>
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
                        <td><?= h($article->status ?? 'draft') ?></td>
                        <td><?= $article->createdAt?->format('Y-m-d') ?? '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>