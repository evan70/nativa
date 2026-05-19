<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 0; background: #f5f5f5; }
        .email-container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .email-header { background: linear-gradient(135deg, #6366f1, #8b5cf6); padding: 40px 30px; text-align: center; }
        .email-header h1 { color: #ffffff; margin: 0; font-size: 24px; }
        .email-body { padding: 30px; }
        .email-body p { color: #374151; line-height: 1.6; margin: 0 0 16px; }
        .email-footer { padding: 20px 30px; background: #f9fafb; border-top: 1px solid #e5e7eb; text-align: center; }
        .email-footer p { color: #9ca3af; font-size: 12px; margin: 0; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Welcome to Marko App!</h1>
        </div>
        <div class="email-body">
            <p>Hi <strong><?= $this->e($name) ?></strong>,</p>
            <p>Thank you for registering! Your account has been created successfully.</p>
            <p>You can now log in and start exploring all the features we have to offer.</p>
            <p style="margin-top: 24px;">
                <a href="/mark/login" style="display: inline-block; padding: 12px 24px; background: #6366f1; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600;">Log In Now</a>
            </p>
        </div>
        <div class="email-footer">
            <p>&copy; <?= date('Y') ?> Marko App. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
