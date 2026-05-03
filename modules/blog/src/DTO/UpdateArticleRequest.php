<?php

declare(strict_types=1);

namespace App\Blog\DTO;

class UpdateArticleRequest
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $content = null,
        public readonly ?string $slug = null,
        public readonly ?string $excerpt = null,
        public readonly ?string $image = null,
        public readonly ?bool $published = null,
        public readonly ?int $categoryId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            content: $data['content'] ?? null,
            slug: $data['slug'] ?? null,
            excerpt: $data['excerpt'] ?? null,
            image: $data['image'] ?? null,
            published: $data['published'] ?? null,
            categoryId: $data['category_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        $data = [];
        if ($this->title !== null) $data['title'] = $this->title;
        if ($this->content !== null) $data['content'] = $this->content;
        if ($this->slug !== null) $data['slug'] = $this->slug;
        if ($this->excerpt !== null) $data['excerpt'] = $this->excerpt;
        if ($this->image !== null) $data['image'] = $this->image;
        if ($this->published !== null) $data['published'] = $this->published;
        if ($this->categoryId !== null) $data['category_id'] = $this->categoryId;
        return $data;
    }
}