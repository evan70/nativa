<?php

declare(strict_types=1);

namespace App\Blog\Controller;

use App\Blog\Contracts\ArticleServiceInterface;
use App\Blog\DTO\ArticleDTO;
use App\Blog\Repository\ArticleRepository;
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

        if ($articles === []) {
            $articles = $this->defaultArticles();
        } else {
            $articles = array_map(
                static fn (object $article): ArticleDTO => ArticleDTO::fromEntity($article),
                $articles,
            );
        }

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
        $article = $this->service->findBySlug($slug);

        if ($article === null) {
            $id = (int) $slug;
            if ($id > 0) {
                $entity = $this->repository->find($id);
                if ($entity !== null) {
                    $article = ArticleDTO::fromEntity($entity);
                }
            }
        }

        if ($article === null) {
            $article = $this->findDefaultArticle($slug);
        }

        if ($article === null) {
            return $this->view->render('blog::article/not-found', [
                'title' => 'Article Not Found',
                'message' => 'The requested article does not exist.',
            ])->withStatus(404);
        }

        return $this->view->render('blog::article/show', [
            'title' => $article->title,
            'article' => $article,
        ]);
    }

    /**
     * @return array<int, ArticleDTO>
     */
    private function defaultArticles(): array
    {
        return [
            new ArticleDTO(
                id: 1,
                title: 'Welcome to PHP CMS',
                content: 'This is your first article. Start creating content!',
                slug: 'welcome-to-php-cms',
                excerpt: 'Your first article',
                image: '/dist/assets/images/afe59aa58f41fc48817094cfe7519d0b.webp',
                published: true,
                status: 'published',
                categoryId: null,
                createdAt: new \DateTimeImmutable('now'),
            ),
            new ArticleDTO(
                id: 2,
                title: 'Nativa PHP + Svelte 5 Architektúra',
                content: 'Naša architektúra kombinuje PHP 8.4+ s DDD prístupom a Svelte 5 komponentmi.',
                slug: 'nativa-php-svelte-architektura',
                excerpt: 'Moderná DDD architektúra s využitím Svelte 5',
                image: '/dist/assets/images/26d7d834d1eda62fc868808f37c9b157.webp',
                published: true,
                status: 'published',
                categoryId: null,
                createdAt: new \DateTimeImmutable('now'),
            ),
            new ArticleDTO(
                id: 3,
                title: 'BEM + Design Tokens v Praxi',
                content: 'Implementovali sme design tokens systém s BEM komponentmi pre konzistentný UI/UX.',
                slug: 'bem-design-tokens-prax',
                excerpt: 'Konzistentný design systém s BEM metódológiou',
                image: '/dist/assets/images/afe59aa58f41fc48817094cfe7519d0b.webp',
                published: true,
                status: 'published',
                categoryId: null,
                createdAt: new \DateTimeImmutable('now'),
            ),
        ];
    }

    private function findDefaultArticle(string $slug): ?ArticleDTO
    {
        foreach ($this->defaultArticles() as $article) {
            if ($article->slug === $slug) {
                return $article;
            }
        }

        return null;
    }
}
