<?php

declare(strict_types=1);

namespace Marko\Mark\Config;

use Marko\Config\ConfigRepositoryInterface;
use Marko\Config\Exceptions\ConfigNotFoundException;

readonly class MarkConfig implements MarkConfigInterface
{
    public function __construct(
        private ConfigRepositoryInterface $config,
    ) {}

    /**
     * @throws ConfigNotFoundException
     */
    public function getGuardName(): string
    {
        return $this->config->getString('mark.guard');
    }

    /**
     * @throws ConfigNotFoundException
     */
    public function getSuperAdminRoleSlug(): string
    {
        return $this->config->getString('mark.super_admin_role');
    }
}
