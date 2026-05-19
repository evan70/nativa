<?php
$currentPage = 'mark';
$this->layout('layouts.mark');

$category = $data['category'] ?? null;
$isEdit = $category !== null && ($category['id'] ?? 0) > 0;
$formAction = $isEdit ? '/mark/categories/' . $category['id'] : '/mark/categories';
$errors = $data['errors'] ?? [];

$name = $category['name'] ?? '';
$slug = $category['slug'] ?? '';
$description = $category['description'] ?? '';
?>

<?php $this->section('content') ?>
<header class="page-header">
    <h1 class="page-header__title"><?= $this->e($title) ?></h1>
    <p class="page-header__subtitle">Mark / Categories / <?= $isEdit ? 'Edit' : 'Create' ?></p>
</header>

<div class="card mark-tag-form-card">
    <div class="card__body">
        <form method="POST" action="<?= $formAction ?>" class="mark-tag-form">
            <?php if ($isEdit): ?>
                <input type="hidden" name="_method" value="PUT">
            <?php endif; ?>

            <div class="form-group">
                <label for="name" class="form-label">Name *</label>
                <input type="text" id="name" name="name" value="<?= $this->e($name) ?>" class="form-input" required>
                <?php if (isset($errors['name'])): ?>
                    <p class="mark-form-error"><?= $this->e($errors['name']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="slug" class="form-label">Slug</label>
                <input type="text" id="slug" name="slug" value="<?= $this->e($slug) ?>" class="form-input" placeholder="auto-generated if empty">
                <p class="form-hint">Used in URL: /articles/categories/your-slug</p>
                <?php if (isset($errors['slug'])): ?>
                    <p class="mark-form-error"><?= $this->e($errors['slug']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" rows="3" class="form-input"><?= $this->e($description) ?></textarea>
            </div>

            <div class="mark-tag-form-actions">
                <button type="submit" class="btn">
                    <?= $isEdit ? 'Update Category' : 'Create Category' ?>
                </button>
                <a href="/mark/categories" class="btn btn--secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php $this->endSection() ?>
