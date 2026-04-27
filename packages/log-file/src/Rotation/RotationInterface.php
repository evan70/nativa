<?php

declare(strict_types=1);

namespace Marko\Log\File\Rotation;

interface RotationInterface
{
    /**
     * Get the current log file path.
     */
    public function getPath(string $basePath, string $channel): string;
}
