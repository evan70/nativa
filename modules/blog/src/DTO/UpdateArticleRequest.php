<?php

declare(strict_types=1);

namespace App\Blog\DTO;

class UpdateArticleRequest
{
    public function __construct(
        public readonly ?string $title,
        public readonly ?string $content,
        public readonly ?string $slug,
        public readonly ?string $excerpt,
        public readonly ?string $image,
        public readonly ?bool $published,
        public readonly ?int $categoryId,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $title = isset($data['title']) && is_string($data['title']) ? $data['title'] : null;
        $content = isset($data['content']) && is_string($data['content']) ? $data['content'] : null;
        $slug = isset($data['slug']) && is_string($data['slug']) ? $data['slug'] : null;
        $excerpt = isset($data['excerpt']) && is_string($data['excerpt']) ? $data['excerpt'] : null;
        $image = isset($data['image']) && is_string($data['image']) ? $data['image'] : null;
        $published = isset($data['published']) ? (is_bool($data['published']) ? $data['published'] : ($data['published'] === '1' || $data['published'] === 1)) : null;
        $categoryId = isset($data['category_id']) && is_numeric($data['category_id']) ? (int) $data['category_id'] : null;

        return new self(
            title: $title,
            content: $content,
            slug: $slug,
            excerpt: $excerpt,
            image: $image,
            published: $published,
            categoryId: $categoryId,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->title !== null) {
            $data['title'] = $this->title;
        }
        if ($this->content !== null) {
            $data['content'] = $this->content;
        }
        if ($this->slug !== null) {
            $data['slug'] = $this->slug;
        }
        if ($this->excerpt !== null) {
            $data['excerpt'] = $this->excerpt;
        }
        if ($this->image !== null) {
            $data['image'] = $this->image;
        }
        if ($this->published !== null) {
            $data['published'] = $this->published;
        }
        if ($this->categoryId !== null) {
            $data['category_id'] = $this->categoryId;
        }
        return $data;
    }
}