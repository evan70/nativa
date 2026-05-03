<?php

declare(strict_types=1);

namespace App\Blog\Service;

use App\Blog\Contracts\ArticleServiceInterface;
use App\Blog\DTO\ArticleDTO;
use App\Blog\DTO\CreateArticleRequest;
use App\Blog\DTO\UpdateArticleRequest;
use App\Blog\Entity\Article;
use App\Blog\Repository\ArticleRepository;

class ArticleService implements ArticleServiceInterface
{
    public function __construct(
        private readonly ArticleRepository $repository,
    ) {}

    public function createArticle(CreateArticleRequest $request): ArticleDTO
    {
        // Check if slug already exists
        $existing = $this->repository->findOneBy(['slug' => $request->slug]);
        if ($existing !== null) {
            throw new \InvalidArgumentException(
                "Article with slug '{$request->slug}' already exists"
            );
        }

        // Create entity
        $article = new Article();
        $article->title = $request->title;
        $article->content = $request->content;
        $article->slug = $request->slug;
        $article->excerpt = $request->excerpt;
        $article->image = $request->image;
        $article->published = $request->published;
        $article->status = $request->published ? 'published' : 'draft';
        $article->categoryId = $request->categoryId;
        $article->createdAt = date('Y-m-d H:i:s');

        $this->repository->save($article);

        return ArticleDTO::fromEntity($article);
    }

    public function updateArticle(int $id, UpdateArticleRequest $request): ArticleDTO
    {
        $article = $this->repository->find($id);
        if ($article === null) {
            throw new \InvalidArgumentException("Article with ID {$id} not found");
        }

        // Check slug uniqueness if changing
        if ($request->slug !== null && $request->slug !== $article->slug) {
            $existing = $this->repository->findOneBy(['slug' => $request->slug]);
            if ($existing !== null && $existing->id !== $id) {
                throw new \InvalidArgumentException(
                    "Article with slug '{$request->slug}' already exists"
                );
            }
            $article->slug = $request->slug;
        }

        // Update fields
        if ($request->title !== null) {
            $article->title = $request->title;
        }
        if ($request->content !== null) {
            $article->content = $request->content;
        }
        if ($request->excerpt !== null) {
            $article->excerpt = $request->excerpt;
        }
        if ($request->image !== null) {
            $article->image = $request->image;
        }
        if ($request->published !== null) {
            $article->published = $request->published;
            $article->status = $request->published ? 'published' : 'draft';
        }
        if ($request->categoryId !== null) {
            $article->categoryId = $request->categoryId;
        }

        $this->repository->save($article);

        return ArticleDTO::fromEntity($article);
    }

    public function deleteArticle(int $id): bool
    {
        $article = $this->repository->find($id);
        if ($article === null) {
            return false;
        }

        $this->repository->delete($article);

        return true;
    }

    public function findBySlug(string $slug): ?ArticleDTO
    {
        $article = $this->repository->findOneBy(['slug' => $slug]);

        if ($article === null) {
            return null;
        }

        return ArticleDTO::fromEntity($article);
    }

    public function findPublished(int $limit = 10, int $offset = 0): array
    {
        $articles = $this->repository->findBy(
            ['published' => true],
            ['createdAt' => 'DESC'],
            $limit,
            $offset,
        );

        return array_map(
            fn(Article $article) => ArticleDTO::fromEntity($article),
            $articles,
        );
    }

    public function findByCategory(int $categoryId, int $limit = 10, int $offset = 0): array
    {
        $articles = $this->repository->findBy(
            ['categoryId' => $categoryId, 'published' => true],
            ['createdAt' => 'DESC'],
            $limit,
            $offset,
        );

        return array_map(
            fn(Article $article) => ArticleDTO::fromEntity($article),
            $articles,
        );
    }
}