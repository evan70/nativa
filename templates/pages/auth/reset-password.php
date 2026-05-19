<?php
$currentPage = 'auth';
$this->layout('layouts.auth');
?>

<?php $this->section('content') ?>
<div class="card card--large">

    <div class="card__header">
        <h1 class="card__title">Set new password</h1>
        <p class="card__subtitle">Enter your email and choose a new password</p>
    </div>

    <div class="card__body">
        <?php if (isset($errors['token'])): ?>
            <div class="notification notification--error">
                <p class="notification__message"><?= $this->e($errors['token']) ?></p>
            </div>
        <?php endif ?>

        <form class="auth-form" method="POST" action="/mark/reset-password/<?= $this->e($token) ?>">
            <input type="hidden" name="_token" value="<?= $this->e($csrfToken ?? '') ?>" />

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
                <?php if (isset($errors['email'])): ?>
                    <p class="form-error"><?= $this->e($errors['email']) ?></p>
                <?php endif ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">New password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input<?= isset($errors['password']) ? ' form-input--error' : '' ?>"
                    required
                    minlength="8"
                />
                <?php if (isset($errors['password'])): ?>
                    <p class="form-error"><?= $this->e($errors['password']) ?></p>
                <?php endif ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm new password</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-input<?= isset($errors['password_confirmation']) ? ' form-input--error' : '' ?>"
                    required
                    minlength="8"
                />
                <?php if (isset($errors['password_confirmation'])): ?>
                    <p class="form-error"><?= $this->e($errors['password_confirmation']) ?></p>
                <?php endif ?>
            </div>

            <button type="submit" class="btn btn--primary btn--block">
                Reset password
            </button>
        </form>
    </div>

    <div class="card__footer">
        <p><a href="/mark/login" class="link">Back to sign in</a></p>
    </div>

</div>
<?php $this->endSection() ?>
