<?php if (!empty($flashMessages)): ?>
    <div class="flash-messages">
        <?php if (isset($flashMessages['success'])): ?>
            <div class="flash-message flash-success" role="alert">
                <?= $this->e($flashMessages['success']) ?>
            </div>
        <?php endif ?>

        <?php if (isset($flashMessages['error'])): ?>
            <div class="flash-message flash-error" role="alert">
                <?= $this->e($flashMessages['error']) ?>
            </div>
        <?php endif ?>
    </div>
<?php endif ?>
