<?php

declare(strict_types=1);

namespace App;

/**
 * Simple static logger for application logging.
 * Writes to storage/framework/logs/app.log
 */
final class AppLogger
{
    private static ?string $logPath = null;

    /**
     * Get the log file path
     */
    private static function getLogPath(): string
    {
        if (self::$logPath === null) {
            $basePath = dirname(__DIR__, 2); // app/src -> project root
            self::$logPath = $basePath . '/storage/framework/logs/app.log';
            
            // Ensure directory exists
            $dir = dirname(self::$logPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        
        return self::$logPath;
    }

    /**
     * Log a message with timestamp
     * @param array<string, mixed> $context
     */
    private static function log(string $level, string $message, array $context = []): void
    {
        // Only log if not explicitly disabled
        $logEnabled = getenv('APP_LOG') !== 'false';
        
        if (!$logEnabled) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $line = sprintf("[%s] %s: %s%s\n", $timestamp, strtoupper($level), $message, $contextStr);
        
        file_put_contents(self::getLogPath(), $line, FILE_APPEND);
    }

    /**
     * Debug level log
     * @param array<string, mixed> $context
     */
    public static function debug(string $message, array $context = []): void
    {
        self::log('debug', $message, $context);
    }

    /**
     * Info level log
     * @param array<string, mixed> $context
     */
    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    /**
     * Warning level log
     * @param array<string, mixed> $context
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }

    /**
     * Error level log
     * @param array<string, mixed> $context
     */
    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }
}
