<?php
$currentPage = 'mark';
$this->layout('layouts.mark');

$allSettings = $data['settings'] ?? [];
$groups = [];
foreach ($allSettings as $setting) {
    $group = $setting['group'] ?? 'general';
    $groups[$group][] = $setting;
}
?>

<?php $this->section('content') ?>
<header class="page-header">
    <h1 class="page-header__title"><?= $this->e($title ?? 'Settings') ?></h1>
    <p class="page-header__subtitle">Mark / Settings</p>
</header>

<div class="card">
    <div class="card__body">
        <div class="mark-tags-toolbar">
            <a href="/mark/settings/new" class="btn">Create New Setting</a>
        </div>
    </div>
</div>

<?php if (empty($allSettings)): ?>
    <div class="card">
        <div class="card__body">
            <p class="form-hint">No settings yet.</p>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($groups as $groupName => $settings): ?>
    <section class="mark-settings-section">
        <h2 class="mark-settings-section__title"><?= $this->e(ucfirst($groupName)) ?></h2>
        <div class="card">
            <div class="card__body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Key</th>
                            <th>Value</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($settings as $setting): ?>
                            <tr>
                                <td><?= $setting['id'] ?></td>
                                <td><code><?= $this->e($setting['key'] ?? '') ?></code></td>
                                <td><?= $this->e(mb_substr($setting['value'] ?? '', 0, 50)) ?></td>
                                <td><?= $this->e($setting['type'] ?? 'string') ?></td>
                                <td>
                                    <div class="mark-tags-actions">
                                        <a href="/mark/settings/<?= $setting['id'] ?>/edit" class="btn btn--sm btn--secondary">Edit</a>
                                        <form method="POST" action="/mark/settings/<?= $setting['id'] ?>" class="mark-tags-delete-form" onsubmit="return confirm('Delete this setting?')">
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
    </section>
    <?php endforeach; ?>
<?php endif; ?>
<?php $this->endSection() ?>
