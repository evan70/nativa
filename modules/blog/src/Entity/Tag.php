<?php

declare(strict_types=1);

namespace App\Blog\Entity;

use Marko\Database\Attributes\Column;
use Marko\Database\Attributes\Table;
use Marko\Database\Entity\Entity;

#[Table('tags')]
class Tag extends Entity
{
    #[Column(primaryKey: true, autoIncrement: true)]
    public ?int $id = null;

    #[Column]
    public string $name = '';

    #[Column]
    public string $slug = '';
}
