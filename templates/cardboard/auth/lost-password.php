<?php $this->layout('cardboard::auth/base') ?>

<?php $this->section('content') ?>
<div class="auth-card">
    <div class="auth-card__header">
        <h1 class="auth-card__title">Reset password</h1>
        <p class="auth-card__subtitle">
            <?php if (!empty($success)): ?>
                Check your email for further instructions
            <?php else: ?>
                Enter your email address and we'll send you a link to reset your password
            <?php endif ?>
        </p>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert--success">
            <p>We've sent password reset instructions to your email address. Please check your inbox and follow the link to reset your password.</p>
            <p style="margin-top: var(--space-3);">Didn't receive the email? Check your spam folder or <a href="<?= $this->e($resetUrl ?? '/mark/forgot-password') ?>" class="link">try again</a>.</p>
        </div>

        <div class="auth-card__footer">
            <p><a href="<?= $this->e($loginUrl ?? '/mark/login') ?>" class="link">Back to sign in</a></p>
        </div>
    <?php else: ?>
        <form class="auth-form" method="POST" action="<?= $this->e($resetUrl ?? '/mark/forgot-password') ?>">
            <input type="hidden" name="_token" value="<?= $this->e($csrfToken ?? '') ?>" />

            <?php if (!empty($error)): ?>
                <div class="alert alert--error">
                    <?= $this->e($error) ?>
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

            <button type="submit" class="btn btn--primary btn--block">
                Send reset link
            </button>
        </form>

        <div class="auth-card__footer">
            <p><a href="<?= $this->e($loginUrl ?? '/mark/login') ?>" class="link">Back to sign in</a></p>
        </div>
    <?php endif ?>
</div>
<?php $this->endSection() ?>
