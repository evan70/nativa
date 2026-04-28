<?php

declare(strict_types=1);

namespace Marko\Mark\Entity;

interface RolePermissionInterface
{
    public function getRoleId(): int;

    public function getPermissionId(): int;
}
