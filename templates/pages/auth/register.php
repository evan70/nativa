<?php
$currentPage = 'auth';
$this->layout('layouts.auth');
?>

<?php $this->section('content') ?>
<div class="card card--large">

    <div class="card__header">
        <h1 class="card__title">Create an account</h1>
        <p class="card__subtitle">Fill in the details below to get started</p>
    </div>

    <div class="card__body">
        <form class="auth-form" method="POST" action="/mark/register">
            <input type="hidden" name="_token" value="<?= $this->e($csrfToken ?? '') ?>" />

            <div class="form-group">
                <label class="form-label" for="name">Full name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-input<?= isset($errors['name']) ? ' form-input--error' : '' ?>"
                    value="<?= $this->e($old['name'] ?? '') ?>"
                    required
                    autofocus
                />
                <?php if (isset($errors['name'])): ?>
                    <p class="form-error"><?= $this->e($errors['name']) ?></p>
                <?php endif ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-input<?= isset($errors['email']) ? ' form-input--error' : '' ?>"
                    value="<?= $this->e($old['email'] ?? '') ?>"
                    required
                />
                <?php if (isset($errors['email'])): ?>
                    <p class="form-error"><?= $this->e($errors['email']) ?></p>
                <?php endif ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
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
                <label class="form-label" for="password_confirmation">Confirm password</label>
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
                Create account
            </button>
        </form>
    </div>

    <div class="card__footer">
        <p>Already have an account? <a href="/mark/login" class="link">Sign in</a></p>
    </div>

</div>
<?php $this->endSection() ?>
