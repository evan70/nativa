<?php

declare(strict_types=1);

namespace App\Blog\Controller;

use App\Blog\Contracts\ArticleServiceInterface;
use App\Blog\Repository\ArticleRepository;
use App\Blog\Entity\Article;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Http\Response;

use Marko\View\ViewInterface;

class ArticleController
{
    public function __construct(
        private readonly ArticleRepository $repository,
        private readonly ArticleServiceInterface $service,
        private readonly ViewInterface $view,
    ) {}

    #[Get('/articles')]
    public function index(): Response
    {
        $limit = (int) ($_GET['limit'] ?? 10);
        $offset = (int) ($_GET['offset'] ?? 0);
        $categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;

        $articles = $categoryId
            ? $this->service->findByCategory($categoryId, $limit, $offset)
            : $this->service->findPublished($limit, $offset);

        return $this->view->render('blog::article/index', [
            'title' => 'Articles',
            'message' => 'Read existing articles.',
            'articles' => $articles,
            'pagination' => [
                'limit' => $limit,
                'offset' => $offset,
            ],
        ]);
    }

    #[Get('/articles/{slug}')]
    public function show(string $slug): Response
    {
        // Try to find by slug first, then by ID
        $article = $this->service->findBySlug($slug);
        
        if ($article === null) {
            // Try as ID
            $id = (int) $slug;
            if ($id > 0) {
                $entity = $this->repository->find($id);
                if ($entity !== null) {
                    return $this->view->render('blog::article/show', [
                        'title' => $entity->title,
                        'article' => $entity,
                    ]);
                }
            }
            
            return $this->view->render('blog::article/not-found', [
                'title' => 'Article Not Found',
                'message' => 'The requested article does not exist.',
            ])->withStatus(404);
        }

        return $this->view->render('blog::article/show', [
            'title' => $article->title,
            'article' => (object) $article->toArray(),
        ]);
    }


}
