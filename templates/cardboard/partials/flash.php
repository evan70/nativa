<?php if (!empty($flashMessages)): ?>
    <div class="flash-messages">
        <?php foreach ($flashMessages as $type => $messages): ?>
            <?php foreach ($messages as $message): ?>
                <div class="notification notification--<?= $this->e($type) ?> notification--visible">
                    <div class="notification__content">
                        <p class="notification__message"><?= $this->e($message) ?></p>
                    </div>
                </div>
            <?php endforeach ?>
        <?php endforeach ?>
    </div>
<?php endif ?>
