<div class="sidebar">
    <div class="sidebar__header">
        <span class="sidebar__brand">Marko Admin</span>
    </div>
    <nav class="sidebar__nav">
        <div class="sidebar__section">
            <a href="/mark" class="sidebar__link sidebar__link--active">
                <svg class="sidebar__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                </svg>
                Dashboard
            </a>
        </div>

        <?php if (!empty($menuItems)): ?>
            <div class="sidebar__section">
                <span class="sidebar__section-title">Sections</span>
                <?php foreach ($menuItems as $item): ?>
                    <a href="<?= $this->e($item['url']) ?>" class="sidebar__link">
                        <?php if (!empty($item['icon'])): ?>
                            <span class="sidebar__icon"><?= $item['icon'] ?></span>
                        <?php endif ?>
                        <?= $this->e($item['label']) ?>
                    </a>
                <?php endforeach ?>
            </div>
        <?php endif ?>

        <div class="sidebar__footer">
            <p class="sidebar__version">Version 0.0.1-alpha</p>
        </div>
    </nav>
</div>