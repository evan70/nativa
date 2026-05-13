<?php

declare(strict_types=1);

namespace App\Blog\Validation;

use App\Blog\Entity\Article;
use App\Blog\Repository\ArticleRepository;

class ArticleValidator
{
    public function __construct(
        private readonly ArticleRepository $repository,
    ) {}

    /**
     * Validate article creation/update data
     * @param array<string, mixed> $data
     * @return array{valid: bool, errors: array<string>}
     */
    public function validate(array $data, ?int $excludeId = null): array
    {
        $errors = [];

        // Title required
        $title = $data['title'] ?? '';
        if (empty($title) || !is_string($title)) {
            $errors['title'] = 'Title is required';
        } elseif (strlen($title) > 255) {
            $errors['title'] = 'Title must be less than 255 characters';
        }

        // Content required
        if (empty($data['content'])) {
            $errors['content'] = 'Content is required';
        }

        // Slug required
        $slug = $data['slug'] ?? '';
        if (empty($slug) || !is_string($slug)) {
            $errors['slug'] = 'Slug is required';
        } elseif (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            $errors['slug'] = 'Slug must contain only lowercase letters, numbers, and hyphens';
        } elseif (strlen($slug) > 255) {
            $errors['slug'] = 'Slug must be less than 255 characters';
        } else {
            // Check uniqueness
            $existing = $this->repository->findOneBy(['slug' => $slug]);
            if ($existing !== null && ($excludeId === null || $existing->id !== $excludeId)) {
                $errors['slug'] = 'Slug already exists';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Validate create request
     * @param array<string, mixed> $data
     * @return array{valid: bool, errors: array<string>}
     */
    public function validateCreate(array $data): array
    {
        return $this->validate($data);
    }

    /**
     * Validate update request
     * @param array<string, mixed> $data
     * @return array{valid: bool, errors: array<string>}
     */
    public function validateUpdate(int $id, array $data): array
    {
        return $this->validate($data, $id);
    }
}