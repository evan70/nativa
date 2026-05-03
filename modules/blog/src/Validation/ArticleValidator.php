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
     * 
     * @return array{valid: bool, errors: array<string>}
     */
    public function validate(array $data, ?int $excludeId = null): array
    {
        $errors = [];

        // Title required
        if (empty($data['title'])) {
            $errors['title'] = 'Title is required';
        } elseif (strlen($data['title']) > 255) {
            $errors['title'] = 'Title must be less than 255 characters';
        }

        // Content required
        if (empty($data['content'])) {
            $errors['content'] = 'Content is required';
        }

        // Slug required
        if (empty($data['slug'])) {
            $errors['slug'] = 'Slug is required';
        } elseif (!preg_match('/^[a-z0-9-]+$/', $data['slug'])) {
            $errors['slug'] = 'Slug must contain only lowercase letters, numbers, and hyphens';
        } elseif (strlen($data['slug']) > 255) {
            $errors['slug'] = 'Slug must be less than 255 characters';
        } else {
            // Check uniqueness
            $existing = $this->repository->findOneBy(['slug' => $data['slug']]);
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
     */
    public function validateCreate(array $data): array
    {
        return $this->validate($data);
    }

    /**
     * Validate update request
     */
    public function validateUpdate(int $id, array $data): array
    {
        return $this->validate($data, $id);
    }
}