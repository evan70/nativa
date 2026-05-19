<?php
$currentPage = 'mark';
$this->layout('layouts.mark');

$setting = $data['setting'] ?? null;
$isEdit = $setting !== null && ($setting['id'] ?? 0) > 0;
$formAction = $isEdit ? '/mark/settings/' . $setting['id'] : '/mark/settings';
$errors = $data['errors'] ?? [];

$key = $setting['key'] ?? '';
$value = $setting['value'] ?? '';
$type = $setting['type'] ?? 'string';
$group = $setting['group'] ?? 'general';
?>

<?php $this->section('content') ?>
<header class="page-header">
    <h1 class="page-header__title"><?= $this->e($title) ?></h1>
    <p class="page-header__subtitle">Mark / Settings / <?= $isEdit ? 'Edit' : 'Create' ?></p>
</header>

<div class="card mark-tag-form-card">
    <div class="card__body">
        <form method="POST" action="<?= $formAction ?>" class="mark-tag-form">
            <?php if ($isEdit): ?>
                <input type="hidden" name="_method" value="PUT">
            <?php endif; ?>

            <?php if (!$isEdit): ?>
            <div class="form-group">
                <label for="key" class="form-label">Key *</label>
                <input type="text" id="key" name="key" value="<?= $this->e($key) ?>" class="form-input" required
                       pattern="[a-z_][a-z0-9_]*"
                       title="Must start with a letter/underscore, lowercase letters/numbers/underscores only">
                <p class="form-hint">Unique identifier for this setting (e.g. app_name, site_title)</p>
                <?php if (isset($errors['key'])): ?>
                    <p class="mark-form-error"><?= $this->e($errors['key']) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="value" class="form-label">Value</label>
                <textarea id="value" name="value" rows="3" class="form-input"><?= $this->e($value) ?></textarea>
                <?php if (isset($errors['value'])): ?>
                    <p class="mark-form-error"><?= $this->e($errors['value']) ?></p>
                <?php endif; ?>
            </div>

            <div class="mark-article-form-row">
                <div class="form-group">
                    <label for="type" class="form-label">Type</label>
                    <select id="type" name="type" class="form-input">
                        <option value="string"<?= $type === 'string' ? ' selected' : '' ?>>String</option>
                        <option value="number"<?= $type === 'number' ? ' selected' : '' ?>>Number</option>
                        <option value="boolean"<?= $type === 'boolean' ? ' selected' : '' ?>>Boolean</option>
                        <option value="json"<?= $type === 'json' ? ' selected' : '' ?>>JSON</option>
                    </select>
                    <?php if (isset($errors['type'])): ?>
                        <p class="mark-form-error"><?= $this->e($errors['type']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="group" class="form-label">Group</label>
                    <input type="text" id="group" name="group" value="<?= $this->e($group) ?>" class="form-input">
                    <p class="form-hint">Group name for organizing settings (e.g. general, email)</p>
                </div>
            </div>

            <div class="mark-tag-form-actions">
                <button type="submit" class="btn">
                    <?= $isEdit ? 'Update Setting' : 'Create Setting' ?>
                </button>
                <a href="/mark/settings" class="btn btn--secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php $this->endSection() ?>
