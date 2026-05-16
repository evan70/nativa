<?php

declare(strict_types=1);

namespace Marko\Mark\Config;

interface AdminPanelConfigInterface
{
    public function getPageTitle(): string;

    public function getItemsPerPage(): int;
}
