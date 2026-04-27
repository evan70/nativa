<?php

declare(strict_types=1);

namespace Marko\Log\File;

use DateTimeImmutable;
use Marko\Log\Config\LogConfig;
use Marko\Log\Contracts\LogFormatterInterface;
use Marko\Log\Contracts\LoggerInterface;
use Marko\Log\Exceptions\LogWriteException;
use Marko\Log\File\Rotation\RotationInterface;
use Marko\Log\LogLevel;
use Marko\Log\LogRecord;

class FileLogger implements LoggerInterface
{
    public function __construct(
        private LogConfig $config,
        private LogFormatterInterface $formatter,
        private RotationInterface $rotation,
    ) {}

    public function emergency(string $message, array $context = []): void
    {
        $this->log(LogLevel::Emergency, $message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->log(LogLevel::Alert, $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log(LogLevel::Critical, $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log(LogLevel::Error, $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevel::Warning, $message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->log(LogLevel::Notice, $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log(LogLevel::Info, $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log(LogLevel::Debug, $message, $context);
    }

    public function log(
        LogLevel $level,
        string $message,
        array $context = [],
    ): void {
        if (!$level->meetsThreshold($this->config->level())) {
            return;
        }

        $record = new LogRecord(
            level: $level,
            message: $message,
            context: $context,
            datetime: new DateTimeImmutable(),
            channel: $this->config->channel(),
        );

        $this->write($record);
    }

    private function write(LogRecord $record): void
    {
        $path = $this->rotation->getPath(
            $this->config->path(),
            $this->config->channel(),
        );

        $directory = dirname($path);

        if (!is_dir($directory)) {
            if (!@mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw LogWriteException::forPath($path, 'Could not create log directory');
            }
        }

        if (!is_writable($directory)) {
            throw LogWriteException::directoryNotWritable($directory);
        }

        $result = @file_put_contents(
            $path,
            $this->formatter->format($record),
            FILE_APPEND | LOCK_EX,
        );

        if ($result === false) {
            throw LogWriteException::forPath($path, 'file_put_contents failed');
        }
    }
}
