<?php

declare(strict_types=1);

namespace Marko\Log\File\Rotation;

class SizeRotation implements RotationInterface
{
    public function __construct(
        private int $maxSize = 10 * 1024 * 1024,
    ) {}

    public function getPath(string $basePath, string $channel): string
    {
        $path = sprintf('%s/%s.log', rtrim($basePath, '/'), $channel);

        if (file_exists($path) && filesize($path) >= $this->maxSize) {
            $this->rotate($path);
        }

        return $path;
    }

    private function rotate(string $path): void
    {
        // Simple rotation: app.log -> app.1.log, app.1.log -> app.2.log, etc.
        // For simplicity, we can just find the first available index or shift them.
        // The docs show app.log, app.1.log, app.2.log.
        
        $info = pathinfo($path);
        $base = $info['dirname'] . '/' . $info['filename'];
        $ext = isset($info['extension']) ? '.' . $info['extension'] : '';

        $i = 1;
        while (file_exists($base . '.' . $i . $ext)) {
            $i++;
        }

        // Shift existing files
        for ($j = $i; $j > 1; $j--) {
            rename($base . '.' . ($j - 1) . $ext, $base . '.' . $j . $ext);
        }

        rename($path, $base . '.1' . $ext);
    }
}
