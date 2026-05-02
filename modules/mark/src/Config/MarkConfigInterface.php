<?php

declare(strict_types=1);

namespace Marko\Mark\Config;

interface MarkConfigInterface
{
    public function getGuardName(): string;

    public function getSuperAdminRoleSlug(): string;
}
