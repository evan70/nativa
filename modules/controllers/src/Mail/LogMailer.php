<?php

declare(strict_types=1);

namespace App\Mail;

use Marko\Mail\Contracts\MailerInterface;
use Marko\Mail\Message;

/**
 * Log-based mailer for development.
 *
 * Writes all outgoing email to a log file instead of actually sending them.
 * Configured via config/mail.php:
 *
 *   'log' => [
 *       'path' => 'storage/logs/mail.log',
 *   ],
 */
class LogMailer implements MailerInterface
{
    private const string DEFAULT_LOG_PATH = 'storage/logs/mail.log';

    /** @var array<string, mixed> */
    private array $config = [];

    /**
     * @param array<string, mixed> $config Log driver configuration
     */
    public function __construct(
        array $config = [],
    ) {
        $this->config = $config;
    }

    public function send(Message $message): bool
    {
        $logDir = $this->resolveLogDir();

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/mail.log';
        $logLine = $this->formatMessage($message);

        file_put_contents(
            $logFile,
            $logLine,
            FILE_APPEND | LOCK_EX,
        );

        error_log('[LogMailer] Email logged: subject="' . ($message->subject ?? '(no subject)') . '", to=' . $this->formatRecipients($message->to));

        return true;
    }

    public function sendRaw(string $to, string $raw): bool
    {
        $logDir = $this->resolveLogDir();

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/mail.log';
        $logLine = sprintf(
            "[%s] Raw Email\n  To: %s\n  Body:\n%s\n\n",
            date('Y-m-d H:i:s'),
            $to,
            $raw,
        );

        file_put_contents(
            $logFile,
            $logLine,
            FILE_APPEND | LOCK_EX,
        );

        error_log('[LogMailer] Raw email logged to: ' . $to);

        return true;
    }

    /**
     * Format a Message object into a human-readable log entry.
     */
    private function formatMessage(Message $message): string
    {
        $lines = [
            '[' . date('Y-m-d H:i:s') . '] Email',
            '  To: ' . $this->formatRecipients($message->to),
        ];

        if ($message->cc !== []) {
            $lines[] = '  Cc: ' . $this->formatRecipients($message->cc);
        }

        if ($message->bcc !== []) {
            $lines[] = '  Bcc: ' . $this->formatRecipients($message->bcc);
        }

        if ($message->from !== null) {
            $fromName = $message->from->name ?? '';
            $lines[] = '  From: ' . ($fromName !== '' ? $fromName . ' <' . $message->from->email . '>' : $message->from->email);
        }

        if ($message->subject !== null) {
            $lines[] = '  Subject: ' . $message->subject;
        }

        if ($message->html !== null) {
            $lines[] = '  HTML: (stripped, ' . strlen($message->html) . ' chars)';

            // Log a plain-text excerpt
            $text = strip_tags($message->html);
            $text = preg_replace('/\s+/', ' ', $text);
            $excerpt = mb_substr(trim($text ?? ''), 0, 200);
            if ($excerpt !== '') {
                $lines[] = '  Excerpt: ' . $excerpt;
            }
        }

        if ($message->text !== null) {
            $excerpt = mb_substr($message->text, 0, 200);
            $lines[] = '  Text: ' . $excerpt;
        }

        if ($message->attachments !== []) {
            $lines[] = '  Attachments: ' . count($message->attachments);
        }

        $lines[] = '';

        return implode("\n", $lines) . "\n";
    }

    /**
     * Format an array of Address objects into a comma-separated string.
     *
     * @param array<\Marko\Mail\Address> $recipients
     */
    private function formatRecipients(array $recipients): string
    {
        $parts = [];

        foreach ($recipients as $addr) {
            if ($addr->name !== null && $addr->name !== '') {
                $parts[] = $addr->name . ' <' . $addr->email . '>';
            } else {
                $parts[] = $addr->email;
            }
        }

        return implode(', ', $parts);
    }

    /**
     * Resolve the log directory path.
     */
    private function resolveLogDir(): string
    {
        $path = $this->config['path'] ?? self::DEFAULT_LOG_PATH;

        if (!is_string($path)) {
            return dirname(__DIR__, 4) . '/storage/logs';
        }

        // If relative, resolve from project root (vendor/marko/../.. = project root)
        if (!str_starts_with($path, '/')) {
            $path = dirname(__DIR__, 4) . '/' . ltrim($path, '/');
        }

        return dirname($path);
    }
}
