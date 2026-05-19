<?php
$currentPage = 'auth';
$this->layout('layouts.auth');
?>

<?php $this->section('content') ?>
<div class="card card--large">

    <div class="card__header">
        <h1 class="card__title">Reset your password</h1>
        <p class="card__subtitle">Enter your email address and we'll send you a reset link</p>
    </div>

    <div class="card__body">
        <?php if (!empty($success)): ?>
            <div class="notification notification--success">
                <p class="notification__message">
                    If an account with that email exists, we've sent a password reset link to it.
                    Please check your email.
                </p>
            </div>
        <?php else: ?>
            <form class="auth-form" method="POST" action="/mark/forgot-password">
                <input type="hidden" name="_token" value="<?= $this->e($csrfToken ?? '') ?>" />

                <?php if (isset($errors['email'])): ?>
                    <div class="notification notification--error">
                        <p class="notification__message"><?= $this->e($errors['email']) ?></p>
                    </div>
                <?php endif ?>

                <div class="form-group">
                    <label class="form-label" for="email">Email address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input<?= isset($errors['email']) ? ' form-input--error' : '' ?>"
                        value="<?= $this->e($old['email'] ?? '') ?>"
                        required
                        autofocus
                    />
                </div>

                <button type="submit" class="btn btn--primary btn--block">
                    Send reset link
                </button>
            </form>
        <?php endif ?>
    </div>

    <div class="card__footer">
        <p>Remember your password? <a href="/mark/login" class="link">Sign in</a></p>
    </div>

</div>
<?php $this->endSection() ?>
