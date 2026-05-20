<?php

declare(strict_types=1);

namespace App\Blog\DTO;

use DateTimeImmutable;

class ArticleDTO
{
    /**
     * @param array<int, array{id: int, name: string, slug: string}> $tags
     */
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
        public readonly ?string $categoryName = null,
        public readonly array $tags = [],
        public readonly ?string $snippet = null,
    ) {}

    public static function fromEntity(object $entity): self
    {
        $id = isset($entity->id) && is_numeric($entity->id) ? (int) $entity->id : null;
        
        // Handle string properties
        $title = '';
        if (isset($entity->title) && is_string($entity->title)) {
            $title = $entity->title;
        } elseif (isset($entity->title) && is_numeric($entity->title)) {
            $title = (string) $entity->title;
        }
        
        $content = '';
        if (isset($entity->content) && is_string($entity->content)) {
            $content = $entity->content;
        } elseif (isset($entity->content) && is_numeric($entity->content)) {
            $content = (string) $entity->content;
        }
        
        $slug = '';
        if (isset($entity->slug) && is_string($entity->slug)) {
            $slug = $entity->slug;
        } elseif (isset($entity->slug) && is_numeric($entity->slug)) {
            $slug = (string) $entity->slug;
        }
        
        $excerpt = '';
        if (isset($entity->excerpt) && is_string($entity->excerpt)) {
            $excerpt = $entity->excerpt;
        } elseif (isset($entity->excerpt) && is_numeric($entity->excerpt)) {
            $excerpt = (string) $entity->excerpt;
        }
        
        $image = '';
        if (isset($entity->image) && is_string($entity->image)) {
            $image = $entity->image;
        } elseif (isset($entity->image) && is_numeric($entity->image)) {
            $image = (string) $entity->image;
        }
        
        $published = false;
        if (isset($entity->published)) {
            $published = is_bool($entity->published) ? $entity->published : ($entity->published === '1' || $entity->published === 1);
        }
        
        $status = 'draft';
        if (isset($entity->status) && is_string($entity->status)) {
            $status = in_array($entity->status, ['published', 'draft', 'archived']) ? $entity->status : 'draft';
        }
        
        $categoryId = null;
        if (isset($entity->categoryId) && is_numeric($entity->categoryId)) {
            $categoryId = (int) $entity->categoryId;
        }
        
        $createdAt = null;
        if (isset($entity->createdAt) && is_string($entity->createdAt)) {
            $createdAt = new DateTimeImmutable($entity->createdAt);
        }

        $categoryName = null;
        if (isset($entity->categoryName) && is_string($entity->categoryName)) {
            $categoryName = $entity->categoryName;
        }

        return new self(
            id: $id,
            title: $title,
            content: $content,
            slug: $slug,
            excerpt: $excerpt,
            image: $image,
            published: $published,
            status: $status,
            categoryId: $categoryId,
            createdAt: $createdAt,
            categoryName: $categoryName,
        );
    }

    /**
     * @return array{id: int|null, title: string, content: string, slug: string, excerpt: string, image: string, published: bool, status: string, category_id: int|null, created_at: string|null}
     */
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