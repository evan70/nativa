<aside class="mark-drawer" data-section="mark-drawer" aria-label="Admin Navigation">
    <div class="mark-drawer__overlay" data-drawer-close aria-hidden="true"></div>
    <div class="mark-drawer__panel" role="dialog" aria-modal="true" aria-label="Navigation menu">
        <div class="mark-drawer__header">
            <span class="mark-drawer__brand">Navigation</span>
            <button class="icon-btn mark-drawer__close" data-drawer-close aria-label="Close Navigation">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <?php if (!empty($menuItems)): ?>
        <nav class="mark-drawer__nav">
            <ul class="mark-drawer__list">
                <?php $i = 0; ?>
                <?php foreach ($menuItems as $item): ?>
                    <?php
                    $isActive = !empty($item['active']);
                    $itemIcon = $item['icon'] ?? 'circle';
                    ?>
                    <li class="mark-drawer__item <?= $isActive ? 'mark-drawer__item--active' : '' ?>">
                        <a href="<?= $this->e($item['url']) ?>" class="mark-drawer__link">
                            <span class="mark-drawer__number"><?= sprintf('%02d', ++$i) ?></span>
                            <span class="mark-drawer__label"><?= $this->e($item['label']) ?></span>
                        </a>
                    </li>
                <?php endforeach ?>
            </ul>
        </nav>
        <?php else: ?>
        <div class="mark-drawer__empty">
            <p class="form-hint">No sections available.</p>
        </div>
        <?php endif ?>

        <div class="mark-drawer__footer">
            <p class="mark-drawer__version">Marko Admin v0.1.0</p>
        </div>
    </div>
</aside>
