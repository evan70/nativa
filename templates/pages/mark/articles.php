<?php
$currentPage = 'mark';
$this->layout('layouts.mark');

$articles = $data['articles'] ?? [];
$allTags = $data['allTags'] ?? [];
$selectedTagId = (int) ($data['selectedTagId'] ?? 0);
$searchQuery = $data['searchQuery'] ?? '';
?>

<?php $this->section('content') ?>
<header class="page-header">
    <h1 class="page-header__title"><?= $this->e($title ?? 'Articles') ?></h1>
    <p class="page-header__subtitle">Mark / Articles</p>
</header>

<div class="card mark-article-toolbar-wrap">
    <div class="card__body mark-article-toolbar">
        <a href="/mark/articles/new" class="btn">Create New Article</a>
    </div>
</div>

<!-- Search + Filter -->
<div class="card">
    <div class="card__body">
        <form method="GET" action="/mark/articles" class="mark-filter-form">
            <div class="mark-filter-row">
                <div class="mark-filter-group">
                    <label for="article-search" class="form-label">Search:</label>
                    <input
                        id="article-search"
                        type="text"
                        name="q"
                        class="form-input"
                        placeholder="Full-text search…"
                        value="<?= $this->e($searchQuery) ?>"
                    >
                </div>
                <div class="mark-filter-group">
                    <label for="tag-filter" class="form-label">Tag:</label>
                    <select id="tag-filter" name="tag_id" class="form-input" onchange="this.form.submit()">
                        <option value="">-- All tags --</option>
                        <?php foreach ($allTags as $tag): ?>
                            <option value="<?= $tag['id'] ?>"<?= $selectedTagId === (int) $tag['id'] ? ' selected' : '' ?>>
                                <?= $this->e($tag['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn">Filter</button>
                <?php if ($searchQuery !== '' || $selectedTagId > 0): ?>
                    <a href="/mark/articles" class="btn btn--sm btn--secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if ($searchQuery !== '' || $selectedTagId > 0): ?>
    <div class="card">
        <div class="card__body">
            <p class="form-hint">
                Showing <?= count($articles) ?> article(s)
                <?php if ($searchQuery !== ''): ?>
                    matching <strong>"<?= $this->e($searchQuery) ?>"</strong>
                <?php endif; ?>
                <?php if ($selectedTagId > 0): ?>
                    <?php
                    $filterTagName = '';
                    foreach ($allTags as $tag) {
                        if ((int) $tag['id'] === $selectedTagId) {
                            $filterTagName = $tag['name'];
                            break;
                        }
                    }
                    ?> in tag <strong><?= $this->e($filterTagName) ?></strong>
                <?php endif; ?>
                &mdash; <a href="/mark/articles" class="link">clear filters</a>
            </p>
        </div>
    </div>
<?php endif; ?>

<?php if (empty($articles)): ?>
    <div class="card">
        <div class="card__body">
            <p class="form-hint">
                <?php if ($searchQuery !== '' && $selectedTagId > 0): ?>
                    No articles matching <strong>"<?= $this->e($searchQuery) ?>"</strong> in the selected tag.
                <?php elseif ($searchQuery !== ''): ?>
                    No articles matching <strong>"<?= $this->e($searchQuery) ?>"</strong>.
                <?php elseif ($selectedTagId > 0): ?>
                    No articles with the selected tag.
                <?php else: ?>
                    No articles yet.
                <?php endif; ?>
            </p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card__body mark-article-table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Tags</th>
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
                                <a href="/articles/<?= $this->e($article->slug ?? $article->id) ?>" class="link mark-article-link">
                                    <?= $this->e($article->title ?? 'Untitled') ?>
                                </a>
                            </td>
                            <td>
                                <?php if (!empty($article->tags)): ?>
                                    <div class="mark-article-tags">
                                        <?php foreach ($article->tags as $tag): ?>
                                            <a href="/mark/articles?tag_id=<?= $tag['id'] ?>" class="link article-tag--sm">
                                                <?= $this->e($tag['name']) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="form-hint">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="mark-article-status mark-article-status--<?= $this->e($article->status ?? 'draft') ?>">
                                    <?= $this->e($article->status ?? 'draft') ?>
                                </span>
                            </td>
                            <td><?= $article->published ? 'Yes' : 'No' ?></td>
                            <td><?= $article->createdAt?->format('Y-m-d') ?? '-' ?></td>
                            <td>
                                <div class="mark-article-actions">
                                    <a href="/mark/articles/<?= $article->id ?>/edit" class="btn btn--sm btn--secondary">Edit</a>
                                    <form method="POST" action="/mark/articles/<?= $article->id ?>" class="mark-article-delete-form" onsubmit="return confirm('Delete this article?')">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="mark-article-delete-btn">Delete</button>
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
