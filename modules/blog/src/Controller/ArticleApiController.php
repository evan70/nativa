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
        $input = $_POST ?? [];
        
        if (empty($input['title']) || empty($input['content']) || empty($input['slug'])) {
            throw new ArticleValidationException('Title, content, and slug are required');
        }

        try {
            $request = new CreateArticleRequest(
                title: $input['title'],
                content: $input['content'],
                slug: $input['slug'],
                excerpt: $input['excerpt'] ?? '',
                image: $input['image'] ?? '',
                published: ($input['published'] ?? 'false') === 'true',
                categoryId: isset($input['category_id']) ? (int) $input['category_id'] : null,
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
        $input = $_POST ?? [];

        try {
            $request = UpdateArticleRequest::fromArray($input);
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
        $limit = (int) ($_GET['limit'] ?? 10);
        $offset = (int) ($_GET['offset'] ?? 0);
        $categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;

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