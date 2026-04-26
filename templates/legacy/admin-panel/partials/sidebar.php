<nav class="admin-sidebar" aria-label="Admin navigation">
    <div class="sidebar-header">
        <span class="sidebar-title">Marko Admin</span>
    </div>

    <ul class="sidebar-menu">
        <?php foreach ($menuItems ?? [] as $item): ?>
            <li class="sidebar-item">
                <a href="<?= $this->e($item->getUrl()) ?>"><?= $this->e($item->getLabel()) ?></a>
            </li>
        <?php endforeach ?>
    </ul>
</nav>
