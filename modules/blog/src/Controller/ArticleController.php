<?php

declare(strict_types=1);

namespace App\Blog\Controller;

use App\Blog\Contracts\ArticleServiceInterface;
use App\Blog\DTO\ArticleDTO;
use App\Blog\Repository\ArticleRepository;
use App\Htmx\HtmxContext;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;

class ArticleController
{
    private const PER_PAGE = 6;

    public function __construct(
        private readonly ArticleRepository $repository,
        private readonly ArticleServiceInterface $service,
        private readonly ViewInterface $view,
    ) {}

    #[Get('/articles')]
    public function index(Request $request): Response
    {
        $htmx = HtmxContext::fromRequest($request);
        $pageInput = $_GET['page'] ?? '1';
        $page = is_numeric($pageInput) ? (int) $pageInput : 1;
        $page = max(1, $page);

        // If HTMX swap request targeting article-list, return partial
        if ($htmx !== null && $htmx->target() === 'article-list') {
            return $this->renderPartial($page);
        }

        $articles = $this->service->findPublished(
            limit: self::PER_PAGE,
            offset: ($page - 1) * self::PER_PAGE
        );
        $articles = array_map(
            static fn (object $article): ArticleDTO => ArticleDTO::fromEntity($article),
            $articles ?: []
        );

        if ($articles === []) {
            $articles = $this->defaultArticles();
        }

        $total = $this->service->countPublished();
        $hasMore = ($page * self::PER_PAGE) < $total;

        return $this->view->render('pages/articles/index', [
            'title' => 'Articles',
            'currentPage' => 'articles',
            'message' => 'Read existing articles.',
            'articles' => $articles,
            'pagination' => [
                'limit' => self::PER_PAGE,
                'page' => $page,
                'has_more' => $hasMore,
                'total' => $total,
            ],
        ]);
    }

    /**
     * Load more articles via HTMX
     * Target: #article-list
     */
    #[Get('/articles/load')]
    public function loadMore(): Response
    {
        $pageInput = $_GET['page'] ?? '2';
        $page = is_numeric($pageInput) ? (int) $pageInput : 2;
        $page = max(1, $page);

        return $this->renderPartial($page);
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
            return Response::html($this->view->renderToString('pages/articles/not-found', [
                'title' => 'Article Not Found',
                'message' => 'The requested article does not exist.',
            ]), 404);
        }

        return $this->view->render('pages/articles/show', [
            'title' => $article->title,
            'article' => $article,
        ]);
    }

    private function renderPartial(int $page): Response
    {
        $articles = $this->service->findPublished(
            limit: self::PER_PAGE,
            offset: ($page - 1) * self::PER_PAGE
        );

        if ($articles === []) {
            return Response::html('');
        }

        $html = $this->renderArticlesHtml($articles, $page);

        return Response::html($html);
    }

    /**
     * @param array<int, \App\Blog\DTO\ArticleDTO> $articles
     */
    private function renderArticlesHtml(array $articles, int $page): string
    {
        $items = '';
        foreach ($articles as $dto) {
            $imageHtml = $dto->image
                ? '<div class="card__image"><img src="' . htmlspecialchars($dto->image) . '" alt="' . htmlspecialchars($dto->title) . '"></div>'
                : '';

            $items .= <<<HTML
                <article class="card card--interactive" data-article-id="{$dto->id}">
                    <a href="/articles/{$dto->slug}" class="card__link">
                        {$imageHtml}
                        <div class="card__body">
                            <h3 class="card__title">{$this->h($dto->title)}</h3>
                            <p class="card__excerpt">{$this->h($dto->excerpt)}</p>
                            <div class="card__meta">
                                <span class="card__date">{$this->formatDate($dto->createdAt)}</span>
                            </div>
                        </div>
                    </a>
                </article>
            HTML;
        }

        $total = $this->service->countPublished();
        $hasMore = ($page * self::PER_PAGE) < $total;

        $loadMore = '';
        if ($hasMore) {
            $nextPage = $page + 1;
            $loadMore = <<<HTML
                <div class="load-more-container">
                    <button
                        class="btn btn--secondary load-more-btn"
                        hx-get="/articles/load?page={$nextPage}"
                        hx-target="#article-list"
                        hx-swap="beforeend"
                        hx-trigger="click"
                        hx-indicator="#load-more-spinner"
                    >
                        <span class="btn__text">Load More</span>
                        <span id="load-more-spinner" class="htmx-indicator">
                            <svg class="spinner" viewBox="0 0 24 24" width="20" height="20">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4 31.4">
                                    <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/>
                                </svg>
                            </span>
                        </span>
                    </button>
                </div>
            HTML;
        }

        // OOB swap: replace the load-more-section so button disappears or updates
        $oob = '<div id="load-more-section" hx-swap-oob="true">' . $loadMore . '</div>';

        return $items . $oob;
    }

    private function h(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }

    private function formatDate(?\DateTimeInterface $date): string
    {
        return $date ? $date->format('j M Y') : '';
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