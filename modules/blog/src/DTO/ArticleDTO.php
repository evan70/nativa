<?php

declare(strict_types=1);

namespace App\Blog\DTO;

use DateTimeImmutable;

class ArticleDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $title,
        public readonly string $content,
        public readonly string $slug,
        public readonly string $excerpt,
        public readonly string $image,
        public readonly bool $published,
        public readonly string $status,
        public readonly ?int $categoryId,
        public readonly ?DateTimeImmutable $createdAt,
    ) {}

    public static function fromEntity(object $entity): self
    {
        return new self(
            id: $entity->id ?? null,
            title: $entity->title,
            content: $entity->content,
            slug: $entity->slug,
            excerpt: $entity->excerpt,
            image: $entity->image,
            published: $entity->published,
            status: $entity->status ?? 'draft',
            categoryId: $entity->categoryId ?? null,
            createdAt: $entity->createdAt 
                ? new DateTimeImmutable($entity->createdAt) 
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'image' => $this->image,
            'published' => $this->published,
            'status' => $this->status,
            'category_id' => $this->categoryId,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
        ];
    }
}