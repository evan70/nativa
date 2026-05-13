<?php

declare(strict_types=1);

namespace App\Blog\DTO;

class CreateArticleRequest
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly string $slug,
        public readonly string $excerpt,
        public readonly string $image,
        public readonly bool $published,
        public readonly ?int $categoryId,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $title = '';
        if (isset($data['title']) && is_string($data['title'])) {
            $title = $data['title'];
        } elseif (isset($data['title']) && is_numeric($data['title'])) {
            $title = (string) $data['title'];
        }

        $content = '';
        if (isset($data['content']) && is_string($data['content'])) {
            $content = $data['content'];
        } elseif (isset($data['content']) && is_numeric($data['content'])) {
            $content = (string) $data['content'];
        }

        $slug = '';
        if (isset($data['slug']) && is_string($data['slug'])) {
            $slug = $data['slug'];
        } elseif (isset($data['slug']) && is_numeric($data['slug'])) {
            $slug = (string) $data['slug'];
        }

        $excerpt = '';
        if (isset($data['excerpt']) && is_string($data['excerpt'])) {
            $excerpt = $data['excerpt'];
        } elseif (isset($data['excerpt']) && is_numeric($data['excerpt'])) {
            $excerpt = (string) $data['excerpt'];
        }

        $image = '';
        if (isset($data['image']) && is_string($data['image'])) {
            $image = $data['image'];
        } elseif (isset($data['image']) && is_numeric($data['image'])) {
            $image = (string) $data['image'];
        }

        $published = false;
        if (isset($data['published'])) {
            $published = is_bool($data['published']) ? $data['published'] : ($data['published'] === '1' || $data['published'] === 1);
        }

        $categoryId = null;
        if (isset($data['category_id']) && is_numeric($data['category_id'])) {
            $categoryId = (int) $data['category_id'];
        }

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
     * @return array{title: string, content: string, slug: string, excerpt: string, image: string, published: bool, category_id: int|null}
     */
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