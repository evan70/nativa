<?php

declare(strict_types=1);

namespace Marko\DevServer\Detection;

class DockerDetector
{
    private const array COMPOSE_FILES = [
        'compose.yaml',
        'compose.yml',
        'docker-compose.yaml',
        'docker-compose.yml',
    ];

    public function __construct(private readonly string $projectRoot) {}

    /** @return array{upCommand: string, downCommand: string}|null */
    public function detect(): ?array
    {
        foreach (self::COMPOSE_FILES as $file) {
            if (file_exists($this->projectRoot . '/' . $file)) {
                // We use 'docker compose' which is the modern command
                return [
                    'upCommand' => 'docker compose up -d',
                    'downCommand' => 'docker compose down',
                ];
            }
        }

        return null;
    }
}
