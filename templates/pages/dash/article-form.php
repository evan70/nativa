<?php

declare(strict_types=1);

use App\Blog\Entity\Article;

// Escape helper function
if (!function_exists('h')) {
    function h(mixed $value): string {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

$article = $data['article'] ?? null;
$isEdit = $article !== null && $article->id > 0;
$title = $isEdit ? 'Edit Article' : 'Create New Article';
$formAction = $isEdit ? '/mark/articles/' . $article->id : '/mark/articles';

// Get categories if available
$categories = $data['categories'] ?? [];

// Helper for form field values
$fv = function(string $field, mixed $default = '') use ($article, $isEdit) {
    if (!$isEdit) return $default;
    return $article->$field ?? $default;
};

// Helper for selected
$selected = function(string $field, string $value) use ($article, $isEdit) {
    if (!$isEdit) return '';
    return ($article->$field ?? '') === $value ? ' selected' : '';
};

// Helper for checkbox
$checked = function(string $field) use ($article, $isEdit) {
    if (!$isEdit) return '';
    return ($article->$field ?? false) ? ' checked' : '';
};

$errors = $data['errors'] ?? [];
?>

<div class="admin-container">
    <div class="admin-header">
        <h1><?= h($title) ?></h1>
        <a href="/mark/articles" class="btn btn--secondary">Back to Articles</a>
    </div>

    <form method="POST" action="<?= $formAction ?>" class="admin-form">
        <?php if ($isEdit): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>

        <div class="form-group">
            <label for="title">Title *</label>
            <input type="text" id="title" name="title" value="<?= h($fv('title')) ?>" required>
            <?php if (isset($errors['title'])): ?>
                <span class="error"><?= h($errors['title']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="slug">Slug</label>
            <input type="text" id="slug" name="slug" value="<?= h($fv('slug')) ?>" placeholder="auto-generated if empty">
            <small>Used in URL: /articles/your-slug</small>
        </div>

        <div class="form-group">
            <label for="excerpt">Excerpt</label>
            <textarea id="excerpt" name="excerpt" rows="3"><?= h($fv('excerpt')) ?></textarea>
            <small>Short description shown in article lists</small>
        </div>

        <div class="form-group">
            <label for="content">Content *</label>
            <textarea id="content" name="content" rows="15" required><?= h($fv('content')) ?></textarea>
            <?php if (isset($errors['content'])): ?>
                <span class="error"><?= h($errors['content']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="image">Image URL</label>
            <input type="text" id="image" name="image" value="<?= h($fv('image')) ?>" placeholder="/dist/assets/images/...">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="draft"<?= $selected('status', 'draft') ?>>Draft</option>
                    <option value="published"<?= $selected('status', 'published') ?>>Published</option>
                    <option value="archived"<?= $selected('status', 'archived') ?>>Archived</option>
                </select>
            </div>

            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="">-- No Category --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category->id ?>"<?= $selected('category_id', (string)$category->id) ?>>
                            <?= h($category->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="published" value="1"<?= $checked('published') ?>>
                <span>Published</span>
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn--primary">
                <?= $isEdit ? 'Update Article' : 'Create Article' ?>
            </button>
            <a href="/mark/articles" class="btn btn--secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
.admin-form {
    max-width: 800px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.form-group input[type="text"],
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.form-group textarea {
    resize: vertical;
}

.form-group small {
    display: block;
    color: #666;
    margin-top: 0.25rem;
    font-size: 0.875rem;
}

.form-group .error {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge--draft { background: #ffc107; color: #000; }
.badge--published { background: #28a745; color: #fff; }
.badge--archived { background: #6c757d; color: #fff; }

.actions {
    display: flex;
    gap: 0.5rem;
}

.btn--small {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.btn--danger {
    background: #dc3545;
    color: #fff;
    border: none;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    cursor: pointer;
}
</style>