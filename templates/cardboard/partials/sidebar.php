<aside class="sidebar">
    <nav class="sidebar__nav">
        <a href="/mark" class="sidebar__link sidebar__link--active">
            <svg class="sidebar__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
            </svg>
            Dashboard
        </a>

        <?php if (!empty($menuItems)): ?>
            <?php foreach ($menuItems as $item): ?>
                <a href="<?= $this->e($item['url']) ?>" class="sidebar__link">
                    <?php if (!empty($item['icon'])): ?>
                        <span class="sidebar__icon"><?= $item['icon'] ?></span>
                    <?php endif ?>
                    <?= $this->e($item['label']) ?>
                </a>
            <?php endforeach ?>
        <?php endif ?>

        <div class="sidebar__footer">
            <p style="font-size: var(--text-xs); color: var(--color-text-muted);">Version 0.0.1-alpha</p>
        </div>
    </nav>
</aside>
