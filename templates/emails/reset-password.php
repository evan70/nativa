<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset your password</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 0; background-color: #f5f5f5;">
    <table role="presentation" style="width: 100%; max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden;">
        <tr>
            <td style="padding: 32px 24px; text-align: center; background: #6366f1;">
                <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Reset your password</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 32px 24px;">
                <p style="margin: 0 0 16px; font-size: 16px; line-height: 1.5; color: #374151;">
                    We received a request to reset the password for your account.
                    Click the button below to set a new password:
                </p>

                <table role="presentation" style="margin: 24px auto;">
                    <tr>
                        <td style="background: #6366f1; border-radius: 6px; padding: 12px 24px; text-align: center;">
                            <a href="<?= $this->e($resetUrl ?? '') ?>"
                               style="color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 600; display: inline-block;">
                                Reset password
                            </a>
                        </td>
                    </tr>
                </table>

                <p style="margin: 16px 0 0; font-size: 14px; line-height: 1.5; color: #6b7280;">
                    This link will expire in 60 minutes. If you didn't request a password reset,
                    you can safely ignore this email.
                </p>

                <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 24px 0;" />

                <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #9ca3af;">
                    If the button above doesn't work, copy and paste this URL into your browser:<br />
                    <span style="color: #6366f1;"><?= $this->e($resetUrl ?? '') ?></span>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
