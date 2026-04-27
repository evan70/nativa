<?php

declare(strict_types=1);

namespace Marko\DevServer\Detection;

class FrontendDetector
{
    private const array LOCK_FILES = [
        'bun.lockb' => 'bun',
        'pnpm-lock.yaml' => 'pnpm',
        'yarn.lock' => 'yarn',
        'package-lock.json' => 'npm',
    ];

    public function __construct(private readonly string $projectRoot) {}

    public function detect(): ?string
    {
        $packageJsonPath = $this->projectRoot . '/package.json';
        if (!file_exists($packageJsonPath)) {
            return null;
        }

        $packageJson = json_decode(file_get_contents($packageJsonPath), true);
        if (!isset($packageJson['scripts']['dev'])) {
            return null;
        }

        foreach (self::LOCK_FILES as $lockFile => $manager) {
            if (file_exists($this->projectRoot . '/' . $lockFile)) {
                return "{$manager} run dev";
            }
        }

        // Default to npm if no lock file found but package.json exists
        return 'npm run dev';
    }
}
