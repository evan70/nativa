<?php

declare(strict_types=1);

namespace App\Blog\Controller;

use App\Blog\Contracts\ArticleServiceInterface;
use App\Blog\DTO\CreateArticleRequest;
use App\Blog\DTO\UpdateArticleRequest;
use App\Blog\Exceptions\ArticleNotFoundException;
use App\Blog\Exceptions\ArticleValidationException;
use Marko\Routing\Attributes\Delete;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Post;
use Marko\Routing\Attributes\Put;
use Marko\Routing\Http\Response;

class ArticleApiController
{
    public function __construct(
        private readonly ArticleServiceInterface $service,
    ) {}

    #[Post('/api/articles')]
    public function create(): Response
    {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $excerpt = $_POST['excerpt'] ?? '';
        $image = $_POST['image'] ?? '';
        $publishedInput = $_POST['published'] ?? 'false';
        $categoryIdInput = $_POST['category_id'] ?? null;

        if (!is_string($title) || $title === '' || !is_string($content) || $content === '' || !is_string($slug) || $slug === '') {
            throw new ArticleValidationException('Title, content, and slug are required');
        }

        try {
            $request = new CreateArticleRequest(
                title: $title,
                content: $content,
                slug: $slug,
                excerpt: is_string($excerpt) ? $excerpt : '',
                image: is_string($image) ? $image : '',
                published: $publishedInput === 'true',
                categoryId: $categoryIdInput !== null && is_numeric($categoryIdInput) ? (int) $categoryIdInput : null,
            );

            $article = $this->service->createArticle($request);

            return Response::json([
                'success' => true,
                'data' => $article->toArray(),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            throw new ArticleValidationException($e->getMessage());
        }
    }

    #[Put('/api/articles/{id}')]
    public function update(int $id): Response
    {
        /** @var array<string, mixed> $validatedInput */
        $validatedInput = $_POST;

        try {
            $request = UpdateArticleRequest::fromArray($validatedInput);
            $article = $this->service->updateArticle($id, $request);

            return Response::json([
                'success' => true,
                'data' => $article->toArray(),
            ]);
        } catch (\InvalidArgumentException $e) {
            throw new ArticleNotFoundException($e->getMessage());
        }
    }

    #[Delete('/api/articles/{id}')]
    public function delete(int $id): Response
    {
        $deleted = $this->service->deleteArticle($id);

        if (!$deleted) {
            throw new ArticleNotFoundException("Article with ID {$id} not found");
        }

        return Response::json([
            'success' => true,
            'message' => 'Article deleted',
        ]);
    }

    #[Get('/api/articles')]
    public function list(): Response
    {
        $limitInput = $_GET['limit'] ?? '10';
        $offsetInput = $_GET['offset'] ?? '0';
        $categoryIdInput = $_GET['category_id'] ?? null;

        $limit = is_numeric($limitInput) ? (int) $limitInput : 10;
        $offset = is_numeric($offsetInput) ? (int) $offsetInput : 0;
        $categoryId = $categoryIdInput !== null && is_numeric($categoryIdInput) ? (int) $categoryIdInput : null;

        $articles = $categoryId 
            ? $this->service->findByCategory($categoryId, $limit, $offset)
            : $this->service->findPublished($limit, $offset);

        return Response::json([
            'success' => true,
            'data' => array_map(fn($a) => $a->toArray(), $articles),
            'pagination' => [
                'limit' => $limit,
                'offset' => $offset,
            ],
        ]);
    }

    #[Get('/api/articles/{slug}')]
    public function show(string $slug): Response
    {
        // Try by slug first, then by ID
        $article = $this->service->findBySlug($slug);
        
        if ($article === null) {
            // Try as ID
            $id = (int) $slug;
            if ($id > 0) {
                $all = $this->service->findPublished(100, 0);
                foreach ($all as $a) {
                    if ($a->id === $id) {
                        return Response::json([
                            'success' => true,
                            'data' => $a->toArray(),
                        ]);
                    }
                }
            }
            throw new ArticleNotFoundException("Article '{$slug}' not found");
        }

        return Response::json([
            'success' => true,
            'data' => $article->toArray(),
        ]);
    }
}