<?php
$currentPage = 'articles';
$this->layout('layouts.admin');

$article = $data['article'] ?? null;
$isEdit = $article !== null && $article->id > 0;
$formAction = $isEdit ? '/mark/articles/' . $article->id : '/mark/articles';
$categories = $data['categories'] ?? [];
$errors = $data['errors'] ?? [];

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
?>

<?php $this->section('content') ?>
<header class="page-header">
    <h1><?= $this->e($title) ?></h1>
    <p class="page-header__subtitle">Dashboard / Articles / <?= $isEdit ? 'Edit' : 'Create' ?></p>
</header>

<div class="card" style="max-width: 800px;">
    <div class="card__body">
        <form method="POST" action="<?= $formAction ?>" style="display: flex; flex-direction: column; gap: var(--space-6);">
            <?php if ($isEdit): ?>
                <input type="hidden" name="_method" value="PUT">
            <?php endif; ?>

            <div class="form-group">
                <label for="title" class="form-label">Title *</label>
                <input type="text" id="title" name="title" value="<?= $this->e($fv('title')) ?>" class="form-input" required>
                <?php if (isset($errors['title'])): ?>
                    <p style="color: var(--color-error); font-size: var(--text-xs); margin: var(--space-1) 0 0;"><?= $this->e($errors['title']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="slug" class="form-label">Slug</label>
                <input type="text" id="slug" name="slug" value="<?= $this->e($fv('slug')) ?>" class="form-input" placeholder="auto-generated if empty">
                <p class="form-hint">Used in URL: /articles/your-slug</p>
            </div>

            <div class="form-group">
                <label for="excerpt" class="form-label">Excerpt</label>
                <textarea id="excerpt" name="excerpt" rows="3" class="form-input"><?= $this->e($fv('excerpt')) ?></textarea>
                <p class="form-hint">Short description shown in article lists</p>
            </div>

            <div class="form-group">
                <label for="content" class="form-label">Content *</label>
                <textarea id="content" name="content" rows="15" class="form-input" required style="font-family: var(--font-mono);"><?= $this->e($fv('content')) ?></textarea>
                <?php if (isset($errors['content'])): ?>
                    <p style="color: var(--color-error); font-size: var(--text-xs); margin: var(--space-1) 0 0;"><?= $this->e($errors['content']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="image" class="form-label">Image URL</label>
                <input type="text" id="image" name="image" value="<?= $this->e($fv('image')) ?>" class="form-input" placeholder="/dist/assets/images/...">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-input">
                        <option value="draft"<?= $selected('status', 'draft') ?>>Draft</option>
                        <option value="published"<?= $selected('status', 'published') ?>>Published</option>
                        <option value="archived"<?= $selected('status', 'archived') ?>>Archived</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="category_id" class="form-label">Category</label>
                    <select id="category_id" name="category_id" class="form-input">
                        <option value="">-- No Category --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category->id ?>"<?= $selected('category_id', (string)$category->id) ?>>
                                <?= $this->e($category->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox">
                    <input type="checkbox" name="published" value="1"<?= $checked('published') ?>>
                    <span>Published</span>
                </label>
            </div>

            <div style="display: flex; gap: var(--space-4); padding-top: var(--space-4); border-top: 1px solid var(--color-border);">
                <button type="submit" class="btn">
                    <?= $isEdit ? 'Update Article' : 'Create Article' ?>
                </button>
                <a href="/mark/articles" class="btn btn--secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php $this->endSection() ?>
