<?php if (!empty($flashMessages)): ?>
    <div class="notification-container" style="position: static; width: 100%; max-width: none; pointer-events: auto; margin-bottom: var(--space-4);">
        <?php foreach ($flashMessages as $type => $messages): ?>
            <?php foreach ($messages as $message): ?>
                <div class="notification notification--<?= $this->e($type) ?> notification--visible" style="transform: none; opacity: 1; margin-bottom: var(--space-2);">
                    <div class="notification__content">
                        <p class="notification__message">
                            <?= $this->e($message) ?>
                        </p>
                    </div>
                </div>
            <?php endforeach ?>
        <?php endforeach ?>
    </div>
<?php endif ?>
