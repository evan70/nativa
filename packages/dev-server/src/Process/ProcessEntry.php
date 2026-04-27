<?php

declare(strict_types=1);

namespace Marko\DevServer\Process;

readonly class ProcessEntry
{
    public function __construct(
        public string $name,
        public int $pid,
        public string $command,
        public int $port,
        public string $startedAt,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'pid' => $this->pid,
            'command' => $this->command,
            'port' => $this->port,
            'startedAt' => $this->startedAt,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['pid'],
            $data['command'],
            $data['port'],
            $data['startedAt'],
        );
    }
}
