<?php

declare(strict_types=1);

namespace Marko\Cardboard\Service;

use App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface;

readonly class PasswordResetService
{
    private const int TOKEN_TTL = 3600; // 60 minutes

    public function __construct(
        private ModuleDatabaseResolverInterface $resolver,
    ) {}

    /**
     * Generate a secure random token and store it in the database.
     */
    public function generateToken(string $email): string
    {
        $token = bin2hex(random_bytes(32));

        $pdo = $this->getConnection();

        // Delete any existing tokens for this email
        $stmt = $pdo->prepare('DELETE FROM "password_resets" WHERE "email" = ?');
        $stmt->execute([$email]);

        // Insert new token
        $stmt = $pdo->prepare(
            'INSERT INTO "password_resets" ("email", "token", "createdAt") VALUES (?, ?, ?)'
        );
        $stmt->execute([$email, $token, date('Y-m-d H:i:s')]);

        error_log('[PasswordReset] Token generated for email=' . $email . ', token=' . $token);

        return $token;
    }

    /**
     * Validate a reset token. Returns the email if valid, null otherwise.
     */
    public function validateToken(string $email, string $token): bool
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare(
            'SELECT "createdAt" FROM "password_resets" WHERE "email" = ? AND "token" = ?'
        );
        $stmt->execute([$email, $token]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            error_log('[PasswordReset] Token not found for email=' . $email);
            return false;
        }

        // Check expiry (60 minutes)
        $createdAtVal = is_array($row) ? ($row['createdAt'] ?? null) : null;
        $createdAt = is_string($createdAtVal) ? strtotime($createdAtVal) : false;
        if ($createdAt === false || (time() - $createdAt) > self::TOKEN_TTL) {
            error_log('[PasswordReset] Token expired for email=' . $email);
            return false;
        }

        return true;
    }

    /**
     * Delete all reset tokens for the given email.
     */
    public function deleteToken(string $email): void
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare('DELETE FROM "password_resets" WHERE "email" = ?');
        $stmt->execute([$email]);

        error_log('[PasswordReset] Tokens deleted for email=' . $email);
    }

    /**
     * Send a password reset email (logs to file in dev).
     */
    public function sendResetEmail(string $email, string $token, string $resetUrl): void
    {
        $logLine = sprintf(
            "[%s] Password Reset Email\n  To: %s\n  Subject: Reset your password\n  Reset URL: %s\n  Token: %s\n\n",
            date('Y-m-d H:i:s'),
            $email,
            $resetUrl,
            $token,
        );

        $logDir = dirname(__DIR__, 4) . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents(
            $logDir . '/mail.log',
            $logLine,
            FILE_APPEND | LOCK_EX,
        );

        error_log('[PasswordReset] Reset email logged: to=' . $email . ', url=' . $resetUrl);
    }

    private function getConnection(): \PDO
    {
        $dbPath = $this->resolver->getDatabasePath('cardboard');
        return new \PDO(
            'sqlite:' . $dbPath,
            null,
            null,
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        );
    }
}
