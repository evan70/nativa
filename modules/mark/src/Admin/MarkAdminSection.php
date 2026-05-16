<?php

declare(strict_types=1);

namespace Marko\Mark\Admin;

use Marko\Admin\Attributes\AdminSection;
use Marko\Admin\Contracts\AdminSectionInterface;
use Marko\Admin\MenuItem;

#[AdminSection(id: 'mark', label: 'Administration', icon: 'settings', sortOrder: 10)]
class MarkAdminSection implements AdminSectionInterface
{
    public function getId(): string
    {
        return 'mark';
    }

    public function getLabel(): string
    {
        return 'Administration';
    }

    public function getIcon(): string
    {
        return 'settings';
    }

    public function getSortOrder(): int
    {
        return 10;
    }

    /**
     * @return array<\Marko\Admin\Contracts\MenuItemInterface>
     */
    public function getMenuItems(): array
    {
        return [
            new MenuItem('mark_dashboard', 'Dashboard', '/mark', 'layout-dashboard', 10),
            new MenuItem('mark_users', 'Users', '/mark/users', 'users', 20),
            new MenuItem('mark_roles', 'Roles', '/mark/roles', 'shield', 30),
            new MenuItem('mark_permissions', 'Permissions', '/mark/permissions', 'key', 40),
        ];
    }
}
