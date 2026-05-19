<?php

declare(strict_types=1);

namespace App\Portfolio\Admin;

use Marko\Admin\Attributes\AdminSection;
use Marko\Admin\Contracts\AdminSectionInterface;
use Marko\Admin\MenuItem;

#[AdminSection(id: 'portfolio', label: 'Portfolio', icon: 'briefcase', sortOrder: 35)]
class PortfolioAdminSection implements AdminSectionInterface
{
    public function getId(): string
    {
        return 'portfolio';
    }

    public function getLabel(): string
    {
        return 'Portfolio';
    }

    public function getIcon(): string
    {
        return 'briefcase';
    }

    public function getSortOrder(): int
    {
        return 35;
    }

    /**
     * @return array<\Marko\Admin\Contracts\MenuItemInterface>
     */
    public function getMenuItems(): array
    {
        return [
            new MenuItem('portfolio_items_index', 'Items', '/admin/portfolio/items', 'list', 10),
            new MenuItem('portfolio_items_create', 'New Item', '/admin/portfolio/items/new', 'plus', 20),
        ];
    }
}
