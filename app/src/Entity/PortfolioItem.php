<?php

declare(strict_types=1);

namespace App\Entity;

use Marko\Database\Attributes\Column;
use Marko\Database\Attributes\Table;
use Marko\Database\Entity\Entity;

#[Table('portfolio_items')]
class PortfolioItem extends Entity
{
    #[Column(primaryKey: true, autoIncrement: true)]
    public ?int $id = null;

    #[Column]
    public string $title = '';

    #[Column]
    public string $slug = '';

    #[Column]
    public string $subtitle = '';

    #[Column(type: 'TEXT')]
    public string $description = '';

    #[Column]
    public string $category = '';

    #[Column]
    public string $role = '';

    #[Column]
    public string $year = '';

    #[Column(type: 'TEXT')]
    public string $stack = '';

    #[Column]
    public string $image = '';

    #[Column('display_order')]
    public int $displayOrder = 0;
}
