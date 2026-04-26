<?php if (!empty($flashMessages)): ?>
    <div class="notification-container notification-container--top-right" style="position: static; width: 100%; max-width: none; margin-bottom: var(--space-4);">
        <?php foreach ($flashMessages as $type => $messages): ?>
            <?php foreach ($messages as $message): ?>
                <div class="notification notification--visible notification--<?= $this->e($type) ?>" style="transform: none; opacity: 1; margin-bottom: var(--space-2);">
                    <div class="notification__content">
                        <p class="notification__message" style="color: inherit;">
                            <?= $this->e($message) ?>
                        </p>
                    </div>
                </div>
            <?php endforeach ?>
        <?php endforeach ?>
    </div>
<?php endif ?>
