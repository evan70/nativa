<?php $this->layout('admin-panel::layout/base') ?>

<?php $this->section('content') ?>
    <h1>Dashboard</h1>

    <p>Welcome to the admin panel.</p>

    <?php if (!empty($sections)): ?>
        <section class="admin-sections">
            <h2>Registered Sections</h2>
            <ul class="sections-list">
                <?php foreach ($sections as $section): ?>
                    <li>
                        <strong><?= $this->e($section->getLabel()) ?></strong>
                    </li>
                <?php endforeach ?>
            </ul>
        </section>
    <?php endif ?>
<?php $this->endSection() ?>
