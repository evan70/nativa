<?php

declare(strict_types=1);

namespace Marko\Log\File\Rotation;

class DailyRotation implements RotationInterface
{
    public function getPath(string $basePath, string $channel): string
    {
        $date = date('Y-m-d');
        return sprintf('%s/%s-%s.log', rtrim($basePath, '/'), $channel, $date);
    }
}
