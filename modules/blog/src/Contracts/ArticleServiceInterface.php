<?php

declare(strict_types=1);

namespace App\Blog\Contracts;

use App\Blog\DTO\ArticleDTO;
use App\Blog\DTO\CreateArticleRequest;
use App\Blog\DTO\UpdateArticleRequest;

interface ArticleServiceInterface
{
    /**
     * Create a new article
     */
    public function createArticle(CreateArticleRequest $request): ArticleDTO;

    /**
     * Update an existing article
     */
    public function updateArticle(int $id, UpdateArticleRequest $request): ArticleDTO;

    /**
     * Delete an article
     */
    public function deleteArticle(int $id): bool;

    /**
     * Find article by slug
     */
    public function findBySlug(string $slug): ?ArticleDTO;

    /**
     * Find all published articles
     */
    public function findPublished(int $limit = 10, int $offset = 0): array;

    /**
     * Find articles by category
     */
    public function findByCategory(int $categoryId, int $limit = 10, int $offset = 0): array;
}