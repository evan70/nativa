<?php
$currentPage = 'mark';
$this->layout('layouts.mark');

$item = $data['item'] ?? null;
$isEdit = $item !== null && ($item['id'] ?? 0) > 0;
$formAction = $isEdit ? '/mark/portfolio/' . $item['id'] : '/mark/portfolio';
$errors = $data['errors'] ?? [];

$title = $item['title'] ?? '';
$slug = $item['slug'] ?? '';
$subtitle = $item['subtitle'] ?? '';
$description = $item['description'] ?? '';
$category = $item['category'] ?? '';
$role = $item['role'] ?? '';
$year = $item['year'] ?? '';
$stack = $item['stack'] ?? '';
$image = $item['image'] ?? '';
$displayOrder = $item['display_order'] ?? 0;
?>

<?php $this->section('content') ?>
<header class="page-header">
    <h1 class="page-header__title"><?= $this->e($title) ?></h1>
    <p class="page-header__subtitle">Mark / Portfolio / <?= $isEdit ? 'Edit' : 'Create' ?></p>
</header>

<div class="card mark-article-form-card">
    <div class="card__body">
        <form method="POST" action="<?= $formAction ?>" class="mark-article-form">
            <?php if ($isEdit): ?>
                <input type="hidden" name="_method" value="PUT">
            <?php endif; ?>

            <div class="form-group">
                <label for="title" class="form-label">Title *</label>
                <input type="text" id="title" name="title" value="<?= $this->e($title) ?>" class="form-input" required>
                <?php if (isset($errors['title'])): ?>
                    <p class="mark-form-error"><?= $this->e($errors['title']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="slug" class="form-label">Slug</label>
                <input type="text" id="slug" name="slug" value="<?= $this->e($slug) ?>" class="form-input" placeholder="auto-generated if empty">
                <?php if (isset($errors['slug'])): ?>
                    <p class="mark-form-error"><?= $this->e($errors['slug']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="subtitle" class="form-label">Subtitle</label>
                <input type="text" id="subtitle" name="subtitle" value="<?= $this->e($subtitle) ?>" class="form-input">
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description *</label>
                <textarea id="description" name="description" rows="5" class="form-input" required><?= $this->e($description) ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <p class="mark-form-error"><?= $this->e($errors['description']) ?></p>
                <?php endif; ?>
            </div>

            <div class="mark-article-form-row">
                <div class="form-group">
                    <label for="category" class="form-label">Category</label>
                    <input type="text" id="category" name="category" value="<?= $this->e($category) ?>" class="form-input">
                </div>
                <div class="form-group">
                    <label for="role" class="form-label">Role</label>
                    <input type="text" id="role" name="role" value="<?= $this->e($role) ?>" class="form-input">
                </div>
            </div>

            <div class="mark-article-form-row">
                <div class="form-group">
                    <label for="year" class="form-label">Year</label>
                    <input type="text" id="year" name="year" value="<?= $this->e($year) ?>" class="form-input">
                </div>
                <div class="form-group">
                    <label for="display_order" class="form-label">Display Order</label>
                    <input type="number" id="display_order" name="display_order" value="<?= (int) $displayOrder ?>" class="form-input">
                </div>
            </div>

            <div class="form-group">
                <label for="stack" class="form-label">Tech Stack</label>
                <input type="text" id="stack" name="stack" value="<?= $this->e($stack) ?>" class="form-input" placeholder="PHP, JavaScript, CSS">
            </div>

            <div class="form-group">
                <label for="image" class="form-label">Image URL</label>
                <input type="text" id="image" name="image" value="<?= $this->e($image) ?>" class="form-input" placeholder="/dist/assets/images/...">
            </div>

            <div class="mark-article-form-actions">
                <button type="submit" class="btn">
                    <?= $isEdit ? 'Update Portfolio Item' : 'Create Portfolio Item' ?>
                </button>
                <a href="/mark/portfolio" class="btn btn--secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php $this->endSection() ?>
