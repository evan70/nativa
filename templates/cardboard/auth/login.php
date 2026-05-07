<?php $this->layout('cardboard::auth/base') ?>

<?php $this->section('content') ?>
<div class="card card--large">

    <div class="card__header">
        <h1 class="card__title">Sign in</h1>
        <p class="card__subtitle">Enter your credentials to access your account</p>
    </div>

    <div class="card__body">
        <form class="auth-form" method="POST" action="<?= $this->e($loginUrl ?? '/mark/login') ?>">
            <input type="hidden" name="_token" value="<?= $this->e($csrfToken ?? '') ?>" />

            <?php if (!empty($error)): ?>
                <div class="notification notification--error">
                    <p class="notification__message"><?= $this->e($error) ?></p>
                </div>
            <?php endif ?>

            <div class="form-group">
                <label class="form-label" for="email">Email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-input"
                    value="<?= $this->e($email ?? '') ?>"
                    required
                    autofocus
                />
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input"
                    required
                />
            </div>

            <div class="form-group form-group--row">
                <label class="checkbox">
                    <input type="checkbox" name="remember" value="1" />
                    <span>Remember me</span>
                </label>
                <a href="<?= $this->e($forgotPasswordUrl ?? '/mark/forgot-password') ?>" class="link">
                    Forgot password?
                </a>
            </div>

            <button type="submit" class="btn btn--primary btn--block">
                Sign in
            </button>
        </form>
    </div>

    <div class="card__footer">
        <p>Don't have an account? <a href="<?= $this->e($registerUrl ?? '/mark/register') ?>" class="link">Register</a></p>
    </div>

</div>
<?php $this->endSection() ?>
