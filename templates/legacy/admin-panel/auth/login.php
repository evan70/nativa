<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->e($pageTitle ?? 'Login - Marko Admin') ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f4f7f9;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            margin-top: 0;
            font-size: 1.5rem;
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
        }
        .login-error {
            background-color: #fee2e2;
            border: 1px solid #ef4444;
            color: #b91c1c;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }
        input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1rem;
        }
        input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 0.5rem;
        }
        button:hover {
            background-color: #2563eb;
        }
    </style>
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
                <input type="email" id="login-email" name="email" required autofocus placeholder="admin@example.com">
            </div>

            <div class="form-group">
                <label for="login-password">Password</label>
                <input type="password" id="login-password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit">Sign In</button>
        </form>
    </div>
</body>
</html>
