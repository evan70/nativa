<?php

declare(strict_types=1);

namespace App\Blog\DTO;

class CreateArticleRequest
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly string $slug,
        public readonly string $excerpt = '',
        public readonly string $image = '',
        public readonly bool $published = false,
        public readonly ?int $categoryId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? '',
            content: $data['content'] ?? '',
            slug: $data['slug'] ?? '',
            excerpt: $data['excerpt'] ?? '',
            image: $data['image'] ?? '',
            published: $data['published'] ?? false,
            categoryId: $data['category_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'image' => $this->image,
            'published' => $this->published,
            'category_id' => $this->categoryId,
        ];
    }
}