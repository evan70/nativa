<?php if (!empty($flashMessages)): ?>
    <div class="flash-container">
        <?php foreach ($flashMessages as $type => $messages): ?>
            <?php foreach ($messages as $message): ?>
                <div class="flash flash--<?= $this->e($type) ?>">
                    <div class="flash__message">
                        <?= $this->e($message) ?>
                    </div>
                </div>
            <?php endforeach ?>
        <?php endforeach ?>
    </div>
<?php endif ?>
