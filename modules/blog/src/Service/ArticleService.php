<?php

declare(strict_types=1);

namespace App\Blog\Service;

use App\Blog\Contracts\ArticleServiceInterface;
use App\Blog\DTO\ArticleDTO;
use App\Blog\DTO\CreateArticleRequest;
use App\Blog\DTO\UpdateArticleRequest;
use App\Blog\Entity\Article;
use App\Blog\Repository\ArticleRepository;
use Marko\Log\Contracts\LoggerInterface;

class ArticleService implements ArticleServiceInterface
{
    private bool $debug;

    public function __construct(
        private readonly ArticleRepository $repository,
        private readonly LoggerInterface $logger,
    ) {
        $this->debug = $_ENV['LOG_LEVEL'] ?? '' === 'debug';
    }

    public function createArticle(CreateArticleRequest $request): ArticleDTO
    {
        if ($this->debug) {
            $this->logger->debug('[BLOG] Creating article', [
                'title' => $request->title,
                'slug' => $request->slug,
            ]);
        }

        // Check if slug already exists
        $existing = $this->repository->findOneBy(['slug' => $request->slug]);
        if ($existing !== null) {
            $this->logger->warning('[BLOG] Article creation failed: slug exists', [
                'slug' => $request->slug,
            ]);
            throw new \InvalidArgumentException("Article with slug '{$request->slug}' already exists");
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

        $this->logger->info('[BLOG] Article created', [
            'id' => $article->id,
            'title' => $article->title,
        ]);

        return ArticleDTO::fromEntity($article);
    }

    public function updateArticle(int $id, UpdateArticleRequest $request): ArticleDTO
    {
        if ($this->debug) {
            $this->logger->debug('[BLOG] Updating article', [
                'id' => $id,
                'data' => $request->toArray(),
            ]);
        }

        $article = $this->repository->find($id);
        if ($article === null) {
            $this->logger->warning('[BLOG] Article update failed: not found', [
                'id' => $id,
            ]);
            throw new \InvalidArgumentException("Article with ID {$id} not found");
        }

        // Check slug uniqueness if changing
        if ($request->slug !== null && $request->slug !== $article->slug) {
            $existing = $this->repository->findOneBy(['slug' => $request->slug]);
            if ($existing !== null && $existing->id !== $id) {
                $this->logger->warning('[BLOG] Article update failed: slug exists', [
                    'id' => $id,
                    'slug' => $request->slug,
                ]);
                throw new \InvalidArgumentException("Article with slug '{$request->slug}' already exists");
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

        $this->logger->info('[BLOG] Article updated', [
            'id' => $article->id,
            'title' => $article->title,
        ]);

        return ArticleDTO::fromEntity($article);
    }

    public function deleteArticle(int $id): bool
    {
        if ($this->debug) {
            $this->logger->debug('[BLOG] Deleting article', ['id' => $id]);
        }

        $article = $this->repository->find($id);
        if ($article === null) {
            $this->logger->warning('[BLOG] Article delete failed: not found', [
                'id' => $id,
            ]);
            return false;
        }

        $this->repository->delete($article);

        $this->logger->info('[BLOG] Article deleted', [
            'id' => $id,
            'title' => $article->title,
        ]);

        return true;
    }

    public function findBySlug(string $slug): ?ArticleDTO
    {
        if ($this->debug) {
            $this->logger->debug('[BLOG] Finding article by slug', ['slug' => $slug]);
        }

        $article = $this->repository->findOneBy(['slug' => $slug]);

        if ($article === null) {
            return null;
        }

        return ArticleDTO::fromEntity($article);
    }

    public function findPublished(int $limit = 10, int $offset = 0): array
    {
        if ($this->debug) {
            $this->logger->debug('[BLOG] Finding published articles', [
                'limit' => $limit,
                'offset' => $offset,
            ]);
        }

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
        if ($this->debug) {
            $this->logger->debug('[BLOG] Finding articles by category', [
                'category_id' => $categoryId,
                'limit' => $limit,
                'offset' => $offset,
            ]);
        }

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