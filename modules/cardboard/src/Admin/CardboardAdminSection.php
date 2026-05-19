<?php

declare(strict_types=1);

namespace Marko\Cardboard\Admin;

use Marko\Admin\Attributes\AdminSection;
use Marko\Admin\Contracts\AdminSectionInterface;
use Marko\Admin\MenuItem;

#[AdminSection(id: 'cardboard', label: 'Cardboard', icon: 'brush', sortOrder: 25)]
class CardboardAdminSection implements AdminSectionInterface
{
    public function getId(): string
    {
        return 'cardboard';
    }

    public function getLabel(): string
    {
        return 'Cardboard';
    }

    public function getIcon(): string
    {
        return 'brush';
    }

    public function getSortOrder(): int
    {
        return 25;
    }

    /**
     * @return array<\Marko\Admin\Contracts\MenuItemInterface>
     */
    public function getMenuItems(): array
    {
        return [
            new MenuItem('cardboard_overview', 'Overview', '/admin/cardboard', 'layout-dashboard', 10),
            new MenuItem('cardboard_settings', 'Settings', '/admin/cardboard/settings', 'sliders', 20),
        ];
    }
}