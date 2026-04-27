<?php

declare(strict_types=1);

namespace Marko\DevServer\Process;

class PidFile
{
    private string $filePath;

    public function __construct(string $projectRoot)
    {
        $this->filePath = $projectRoot . '/.marko/dev.json';
    }

    /** @param array<ProcessEntry> $entries */
    public function write(array $entries): void
    {
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $data = array_map(fn(ProcessEntry $entry) => $entry->toArray(), $entries);
        file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    /** @return array<ProcessEntry> */
    public function read(): array
    {
        if (!file_exists($this->filePath)) {
            return [];
        }

        $data = json_decode(file_get_contents($this->filePath), true);
        if (!is_array($data)) {
            return [];
        }

        return array_map(fn(array $item) => ProcessEntry::fromArray($item), $data);
    }

    public function clear(): void
    {
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }

    public function isRunning(int $pid): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // Simplified check for Windows
            $output = [];
            exec("tasklist /FI \"PID eq $pid\"", $output);
            return count($output) > 1;
        }

        return posix_kill($pid, 0);
    }
}
