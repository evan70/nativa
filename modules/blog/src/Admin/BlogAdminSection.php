<?php

declare(strict_types=1);

namespace App\Blog\Admin;

use Marko\Admin\Attributes\AdminSection;
use Marko\Admin\Contracts\AdminSectionInterface;
use Marko\Admin\MenuItem;

#[AdminSection(id: 'blog', label: 'Blog', icon: 'book', sortOrder: 30)]
class BlogAdminSection implements AdminSectionInterface
{
    public function getId(): string
    {
        return 'blog';
    }

    public function getLabel(): string
    {
        return 'Blog';
    }

    public function getIcon(): string
    {
        return 'book';
    }

    public function getSortOrder(): int
    {
        return 30;
    }

    /**
     * @return array<\Marko\Admin\Contracts\MenuItemInterface>
     */
    public function getMenuItems(): array
    {
        return [
            new MenuItem('articles_index', 'Articles', '/mark/articles', 'list', 10),
            new MenuItem('articles_create', 'New Article', '/mark/articles/new', 'plus', 20),
        ];
    }
}
