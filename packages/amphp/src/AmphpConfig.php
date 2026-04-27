<?php

declare(strict_types=1);

namespace Marko\Amphp;

use Marko\Config\ConfigInterface;

class AmphpConfig
{
    public function __construct(
        private readonly ConfigInterface $config
    ) {}

    public function isEnabled(): bool
    {
        return (bool) $this->config->get('amphp.enabled', false);
    }

    public function getConcurrency(): int
    {
        return (int) $this->config->get('amphp.concurrency', 10);
    }
}
