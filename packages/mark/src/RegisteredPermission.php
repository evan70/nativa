<?php

declare(strict_types=1);

namespace Marko\Mark;

readonly class RegisteredPermission
{
    public function __construct(
        public string $key,
        public string $label,
        public string $group,
    ) {}
}
