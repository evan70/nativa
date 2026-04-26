<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->e($pageTitle ?? 'Login - Marko Admin') ?></title>
</head>
<body class="admin-login">
    <div class="login-container">
        <h1>Admin Login</h1>

        <?php if (!empty($error)): ?>
            <div class="login-error" role="alert"><?= $this->e($error) ?></div>
        <?php endif ?>

        <form method="post" action="<?= $this->e($loginUrl ?? '/mark/login') ?>">
            <input type="hidden" name="_token" value="<?= $this->e($csrfToken ?? '') ?>">

            <div class="form-group">
                <label for="login-email">Email</label>
                <input type="email" id="login-email" name="email" required autofocus>
            </div>

            <div class="form-group">
                <label for="login-password">Password</label>
                <input type="password" id="login-password" name="password" required>
            </div>

            <button type="submit">Sign In</button>
        </form>
    </div>
</body>
</html>
