<?php $this->layout('cardboard::auth/base') ?>

<?php $this->section('content') ?>
<div class="auth-card">
    <div class="auth-card__header">
        <h1 class="auth-card__title">Create an account</h1>
        <p class="auth-card__subtitle">Fill in your details to get started</p>
    </div>

    <form class="auth-form" method="POST" action="<?= $this->e($registerUrl ?? '/mark/register') ?>">
        <input type="hidden" name="_token" value="<?= $this->e($csrfToken ?? '') ?>" />

        <?php if (!empty($error)): ?>
            <div class="alert alert--error">
                <?= $this->e($error) ?>
            </div>
        <?php endif ?>

        <?php if (!empty($errors) && is_array($errors)): ?>
            <div class="alert alert--error">
                <ul>
                    <?php foreach ($errors as $field => $message): ?>
                        <li><?= $this->e($message) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>

        <div class="form-group">
            <label class="form-label" for="name">Full name</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                class="form-input" 
                value="<?= $this->e($name ?? '') ?>"
                required 
                autofocus
            />
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email address</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                class="form-input" 
                value="<?= $this->e($email ?? '') ?>"
                required
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
            <p class="form-hint">Must be at least 8 characters long</p>
        </div>

        <div class="form-group">
            <label class="form-label" for="password_confirmation">Confirm password</label>
            <input 
                type="password" 
                id="password_confirmation" 
                name="password_confirmation" 
                class="form-input" 
                required
            />
        </div>

        <div class="form-group">
            <label class="checkbox">
                <input type="checkbox" name="terms" value="1" required />
                <span>I agree to the <a href="/terms" class="link" target="_blank">Terms of Service</a> and <a href="/privacy" class="link" target="_blank">Privacy Policy</a></span>
            </label>
        </div>

        <button type="submit" class="btn btn--primary btn--block">
            Create account
        </button>
    </form>

    <div class="auth-card__footer">
        <p>Already have an account? <a href="<?= $this->e($loginUrl ?? '/mark/login') ?>" class="link">Sign in</a></p>
    </div>
</div>
<?php $this->endSection() ?>
