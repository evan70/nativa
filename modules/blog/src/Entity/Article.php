<?php

declare(strict_types=1);

namespace App\Blog\Entity;

use Marko\Database\Attributes\Column;
use Marko\Database\Attributes\Table;
use Marko\Database\Entity\Entity;

#[Table('articles')]
class Article extends Entity
{
    #[Column(primaryKey: true, autoIncrement: true)]
    public ?int $id = null;

    #[Column]
    public string $title = '';

    #[Column(type: 'TEXT')]
    public string $content = '';

    #[Column]
    public bool $published = false;

    #[Column]
    public string $slug = '';

    #[Column(type: 'TEXT')]
    public string $excerpt = '';

    #[Column(type: 'string')]
    public string $image = '';

    #[Column]
    public string $status = 'published';

    #[Column('category_id')]
    public ?int $categoryId = null;

    #[Column('created_at')]
    public ?string $createdAt = null;
}