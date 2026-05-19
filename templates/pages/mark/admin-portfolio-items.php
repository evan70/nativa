<?php
$currentPage = 'mark';
$this->layout('layouts.mark');

$items = $data['items'] ?? [];
?>

<?php $this->section('content') ?>
<header class="page-header">
    <h1 class="page-header__title"><?= $this->e($title ?? 'Portfolio Items') ?></h1>
    <p class="page-header__subtitle">Mark / Portfolio</p>
</header>

<div class="card">
    <div class="card__body">
        <div class="mark-tags-toolbar">
            <a href="/mark/portfolio/new" class="btn">Create New Portfolio Item</a>
        </div>
    </div>
</div>

<?php if (empty($items)): ?>
    <div class="card">
        <div class="card__body">
            <p class="form-hint">No portfolio items yet.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card__body">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Year</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= $item['id'] ?></td>
                            <td><strong><?= $this->e($item['title'] ?? 'Untitled') ?></strong></td>
                            <td><?= $this->e($item['category'] ?? '-') ?></td>
                            <td><?= $this->e($item['year'] ?? '-') ?></td>
                            <td><?= $this->e($item['role'] ?? '-') ?></td>
                            <td>
                                <div class="mark-tags-actions">
                                    <a href="/mark/portfolio/<?= $item['id'] ?>/edit" class="btn btn--sm btn--secondary">Edit</a>
                                    <form method="POST" action="/mark/portfolio/<?= $item['id'] ?>" class="mark-tags-delete-form" onsubmit="return confirm('Delete this portfolio item?')">
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
