<?php

declare(strict_types=1);

namespace Marko\DevServer\Process;

use Marko\DevServer\Exceptions\DevServerException;
use Symfony\Component\Process\Process;

class ProcessManager
{
    /** @var array<string, Process> */
    private array $processes = [];

    public function __construct(
        private readonly PidFile $pidFile,
        private readonly int $port = 8000,
    ) {}

    /** @throws DevServerException */
    public function start(string $name, string $command, bool $detached = true): int
    {
        if (!$detached) {
            $process = Process::fromShellCommandline($command);
            $process->setTimeout(null);
            $process->start(function ($type, $buffer) use ($name) {
                echo "[{$name}] {$buffer}";
            });

            if (!$process->isRunning()) {
                throw DevServerException::processFailedToStart($name, $command);
            }

            $pid = $process->getPid();
            if ($pid === null) {
                throw DevServerException::processFailedToStart($name, $command);
            }
            $this->processes[$name] = $process;
        } else {
            if (PHP_OS_FAMILY === 'Windows') {
                // On Windows, we use 'start /B' to run in background
                $fullCommand = "start /B {$command}";
                $handle = popen($fullCommand, 'r');
                $pid = 0; // Difficult to get PID on Windows without more complexity
            } else {
                // On Unix, we use nohup and &
                $fullCommand = "nohup {$command} > /dev/null 2>&1 & echo $!";
                $output = [];
                exec($fullCommand, $output);
                $pid = (int) ($output[0] ?? 0);
            }
        }

        if ($pid === 0 && PHP_OS_FAMILY !== 'Windows') {
            throw DevServerException::processFailedToStart($name, $command);
        }

        $entries = $this->pidFile->read();
        $entries[] = new ProcessEntry(
            $name,
            $pid,
            $command,
            $this->port,
            date('Y-m-d H:i:s')
        );
        $this->pidFile->write($entries);

        return $pid;
    }

    public function stop(string $name): void
    {
        $entries = $this->pidFile->read();
        $newEntries = [];
        foreach ($entries as $entry) {
            if ($entry->name === $name) {
                if ($entry->pid > 0 && $this->pidFile->isRunning($entry->pid)) {
                    $this->killProcess($entry->pid);
                }
            } else {
                $newEntries[] = $entry;
            }
        }
        $this->pidFile->write($newEntries);

        if (isset($this->processes[$name])) {
            $this->processes[$name]->stop();
            unset($this->processes[$name]);
        }
    }

    public function stopAll(): void
    {
        $entries = $this->pidFile->read();
        foreach ($entries as $entry) {
            if ($entry->pid > 0 && $this->pidFile->isRunning($entry->pid)) {
                $this->killProcess($entry->pid);
            }
        }
        $this->pidFile->clear();

        foreach ($this->processes as $process) {
            $process->stop();
        }
        $this->processes = [];
    }

    private function killProcess(int $pid): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            exec("taskkill /F /T /PID {$pid}");
        } else {
            posix_kill($pid, SIGTERM);
            // Give it a moment and then SIGKILL if still running
            usleep(100000);
            if (posix_kill($pid, 0)) {
                posix_kill($pid, SIGKILL);
            }
        }
    }

    public function getPid(string $name): ?int
    {
        $entries = $this->pidFile->read();
        foreach ($entries as $entry) {
            if ($entry->name === $name) {
                return $entry->pid;
            }
        }
        return null;
    }

    public function getPids(): array
    {
        $entries = $this->pidFile->read();
        $pids = [];
        foreach ($entries as $entry) {
            $pids[$entry->name] = $entry->pid;
        }
        return $pids;
    }

    public function isRunning(string $name): bool
    {
        $pid = $this->getPid($name);
        return $pid !== null && $pid > 0 && $this->pidFile->isRunning($pid);
    }

    public function runForeground(): void
    {
        while (true) {
            $anyRunning = false;
            foreach ($this->processes as $process) {
                if ($process->isRunning()) {
                    $anyRunning = true;
                    break;
                }
            }

            if (!$anyRunning) {
                break;
            }

            usleep(100000);
        }
    }
}
