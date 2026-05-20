<?php

declare(strict_types=1);

namespace App\Blog\Controller;

use App\Blog\Contracts\ArticleServiceInterface;
use App\Blog\Database\BlogConnection;
use App\Blog\DTO\ArticleDTO;
use App\Blog\Repository\ArticleRepository;
use App\Htmx\HtmxContext;
use Marko\Database\Connection\ConnectionInterface;
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
        private readonly BlogConnection $blogConnection,
    ) {}

    #[Get('/articles')]
    public function index(Request $request): Response
    {
        $htmx = HtmxContext::fromRequest($request);
        $pageInput = $_GET['page'] ?? '1';
        $page = is_numeric($pageInput) ? (int) $pageInput : 1;
        $page = max(1, $page);

        $q = $_GET['q'] ?? '';
        $searchQuery = trim(is_string($q) ? $q : '');
        $tag = $_GET['tag'] ?? '';
        $tagSlug = trim(is_string($tag) ? $tag : '');
        $cat = $_GET['category'] ?? '';
        $categoryName = trim(is_string($cat) ? $cat : '');

        // If HTMX swap request targeting article-list, return partial
        if ($htmx !== null && $htmx->target() === 'article-list') {
            return $this->renderPartial($page, $searchQuery, $tagSlug, $categoryName);
        }

        // Handle FTS search
        if ($searchQuery !== '') {
            return $this->renderSearchResults($searchQuery, $tagSlug, $page, $categoryName);
        }

        // Handle tag filter
        if ($tagSlug !== '') {
            return $this->filterByTag($tagSlug, $page, $categoryName);
        }

        // Handle category filter
        if ($categoryName !== '') {
            return $this->filterByCategory($categoryName, $page);
        }

        $allTags = $this->fetchAllTags();
        $allCategories = $this->fetchAllCategories();

        $articleEntities = $this->service->findPublished(
            limit: self::PER_PAGE,
            offset: ($page - 1) * self::PER_PAGE
        );

        $articles = array_map(
            fn (object $a): ArticleDTO => $this->attachTagsToDto(ArticleDTO::fromEntity($a)),
            $articleEntities ?: []
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
            'searchQuery' => '',
            'tagSlug' => $tagSlug,
            'categoryName' => $categoryName,
            'allTags' => $allTags,
            'allCategories' => $allCategories,
            'pagination' => [
                'limit' => self::PER_PAGE,
                'page' => $page,
                'has_more' => $hasMore,
                'total' => $total,
            ],
        ]);
    }

    /**
     * Render FTS search results with snippet highlighting.
     */
    private function renderSearchResults(string $searchQuery, string $tagSlug, int $page, string $categoryName = ''): Response
    {
        $ftsResults = $this->searchArticlesByFts($searchQuery);
        $matchedIds = $ftsResults['ids'];
        $snippets = $ftsResults['snippets'];

        // If tag filter is also active, intersect with tag-matched IDs
        if ($tagSlug !== '') {
            $taggedIds = $this->getArticleIdsByTagSlug($tagSlug);
            $matchedIds = array_values(array_intersect($matchedIds, $taggedIds));
        }

        // If category filter is also active, intersect with category-matched IDs
        if ($categoryName !== '') {
            $catId = $this->findCategoryId($categoryName);
            if ($catId !== null) {
                $catIds = $this->getArticleIdsByCategoryId($catId);
                $matchedIds = array_values(array_intersect($matchedIds, $catIds));
            } else {
                $matchedIds = [];
            }
        }

        $total = count($matchedIds);
        $pageIds = array_slice($matchedIds, ($page - 1) * self::PER_PAGE, self::PER_PAGE);

        // Fetch articles by matched IDs
        $articles = [];
        foreach ($pageIds as $id) {
            $entity = $this->repository->find($id);
            if ($entity === null) {
                continue;
            }
            $dto = ArticleDTO::fromEntity($entity);
            $dto = $this->attachTagsToDto($dto);

            // Attach snippet via constructor copy
            $snippet = $snippets[$id] ?? null;
            $articles[] = new ArticleDTO(
                id: $dto->id,
                title: $dto->title,
                content: $dto->content,
                slug: $dto->slug,
                excerpt: $dto->excerpt,
                image: $dto->image,
                published: $dto->published,
                status: $dto->status,
                categoryId: $dto->categoryId,
                createdAt: $dto->createdAt,
                categoryName: $dto->categoryName,
                tags: $dto->tags,
                snippet: $snippet,
            );
        }

        $hasMore = ($page * self::PER_PAGE) < $total;

        $title = 'Search: "' . $searchQuery . '"';
        if ($tagSlug !== '') {
            $title .= ' (tag: ' . $tagSlug . ')';
        }

        return $this->view->render('pages/articles/index', [
            'title' => $title,
            'currentPage' => 'articles',
            'message' => 'Search results for: ' . $searchQuery,
            'articles' => $articles,
            'searchQuery' => $searchQuery,
            'tagSlug' => $tagSlug,
            'categoryName' => $categoryName,
            'allTags' => $this->fetchAllTags(),
            'allCategories' => $this->fetchAllCategories(),
            'pagination' => [
                'limit' => self::PER_PAGE,
                'page' => $page,
                'has_more' => $hasMore,
                'total' => $total,
            ],
        ]);
    }

    /**
     * Get article IDs that have a tag with the given slug.
     *
     * @return array<int>
     */
    private function getArticleIdsByTagSlug(string $tagSlug): array
    {
        $rows = $this->getDbConnection()->query(
            'SELECT at.article_id FROM article_tags at INNER JOIN tags t ON t.id = at.tag_id WHERE t.slug = ?',
            [$tagSlug],
        );
        return array_map(fn (array $row): int => is_numeric($row['article_id'] ?? null) ? (int) $row['article_id'] : 0, $rows);
    }

    /**
     * Load more articles via HTMX (supports search + tag + category params).
     */
    #[Get('/articles/load')]
    public function loadMore(): Response
    {
        $pageInput = $_GET['page'] ?? '2';
        $page = is_numeric($pageInput) ? (int) $pageInput : 2;
        $page = max(1, $page);

        $q = $_GET['q'] ?? '';
        $searchQuery = trim(is_string($q) ? $q : '');
        $tag = $_GET['tag'] ?? '';
        $tagSlug = trim(is_string($tag) ? $tag : '');
        $cat = $_GET['category'] ?? '';
        $categoryName = trim(is_string($cat) ? $cat : '');

        return $this->renderPartial($page, $searchQuery, $tagSlug, $categoryName);
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

        // Attach tags to the article
        $article = $this->attachTagsToDto($article);

        return $this->view->render('pages/articles/show', [
            'title' => $article->title,
            'article' => $article,
        ]);
    }

    private function renderPartial(int $page, string $searchQuery = '', string $tagSlug = '', string $categoryName = ''): Response
    {
        if ($searchQuery !== '') {
            $ftsResults = $this->searchArticlesByFts($searchQuery);
            $matchedIds = $ftsResults['ids'];
            $snippets = $ftsResults['snippets'];

            if ($tagSlug !== '') {
                $taggedIds = $this->getArticleIdsByTagSlug($tagSlug);
                $matchedIds = array_values(array_intersect($matchedIds, $taggedIds));
            }

            if ($categoryName !== '') {
                $catId = $this->findCategoryId($categoryName);
                if ($catId !== null) {
                    $catIds = $this->getArticleIdsByCategoryId($catId);
                    $matchedIds = array_values(array_intersect($matchedIds, $catIds));
                } else {
                    $matchedIds = [];
                }
            }

            $pageIds = array_slice($matchedIds, ($page - 1) * self::PER_PAGE, self::PER_PAGE);

            $articles = [];
            foreach ($pageIds as $id) {
                $entity = $this->repository->find($id);
                if ($entity === null) {
                    continue;
                }
                $dto = ArticleDTO::fromEntity($entity);
                $dto = $this->attachTagsToDto($dto);
                $articles[] = new ArticleDTO(
                    id: $dto->id,
                    title: $dto->title,
                    content: $dto->content,
                    slug: $dto->slug,
                    excerpt: $dto->excerpt,
                    image: $dto->image,
                    published: $dto->published,
                    status: $dto->status,
                    categoryId: $dto->categoryId,
                    createdAt: $dto->createdAt,
                    categoryName: $dto->categoryName,
                    tags: $dto->tags,
                    snippet: $snippets[$id] ?? null,
                );
            }


            if ($articles === []) {
                return Response::html('');
            }

            $html = $this->renderArticlesHtml($articles, $page, $searchQuery, $tagSlug, $categoryName);
            return Response::html($html);
        }

        if ($tagSlug !== '') {
            $allPublished = $this->service->findPublished(limit: 100, offset: 0);
            $filtered = [];
            foreach ($allPublished as $article) {
                $dto = $this->attachTagsToDto(ArticleDTO::fromEntity($article));
                foreach ($dto->tags as $tag) {
                    if ($tag['slug'] === $tagSlug) {
                        $filtered[] = $dto;
                        break;
                    }
                }
            }

            if ($categoryName !== '' && $filtered !== []) {
                $catId = $this->findCategoryId($categoryName);
                if ($catId !== null) {
                    $filtered = array_values(array_filter(
                        $filtered,
                        fn (ArticleDTO $a): bool => $a->categoryId === $catId,
                    ));
                } else {
                    $filtered = [];
                }
            }

            $pageArticles = array_slice($filtered, ($page - 1) * self::PER_PAGE, self::PER_PAGE);
            if ($pageArticles === []) {
                return Response::html('');
            }
            return Response::html($this->renderArticlesHtml($pageArticles, $page, '', $tagSlug, $categoryName));
        }

        if ($categoryName !== '') {
            $catId = $this->findCategoryId($categoryName);
            if ($catId === null) {
                return Response::html('');
            }
            $allPublished = $this->service->findPublished(limit: 100, offset: 0);
            $filtered = array_values(array_filter(
                $allPublished,
                fn (object $a): bool => $a->categoryId === $catId,
            ));
            $dtoArticles = array_map(
                fn (object $a): ArticleDTO => $this->attachTagsToDto(ArticleDTO::fromEntity($a)),
                $filtered
            );
            $pageArticles = array_slice($dtoArticles, ($page - 1) * self::PER_PAGE, self::PER_PAGE);
            if ($pageArticles === []) {
                return Response::html('');
            }
            return Response::html($this->renderArticlesHtml($pageArticles, $page, '', '', $categoryName));
        }

        $articleEntities = $this->service->findPublished(
            limit: self::PER_PAGE,
            offset: ($page - 1) * self::PER_PAGE
        );

        if ($articleEntities === []) {
            return Response::html('');
        }

        $html = $this->renderArticlesHtml($articleEntities, $page);
        return Response::html($html);
    }

    /**
     * @param array<int, ArticleDTO> $articles
     */
    private function renderArticlesHtml(array $articles, int $page, string $searchQuery = '', string $tagSlug = '', string $categoryName = ''): string
    {
        $items = '';
        foreach ($articles as $dto) {
            $dto = $this->attachTagsToDto($dto);

            $imageHtml = $dto->image
                ? '<div class="card__image"><img src="' . htmlspecialchars($dto->image) . '" alt="' . htmlspecialchars($dto->title) . '"></div>'
                : '';

            $tagsHtml = '';
            if (!empty($dto->tags)) {
                $tagLinks = array_map(
                    fn (array $t): string => '<a href="/articles?tag=' . htmlspecialchars($t['slug']) . '" class="article-tag">' . htmlspecialchars($t['name']) . '</a>',
                    $dto->tags,
                );
                $tagsHtml = '<div class="article-tags">' . implode('', $tagLinks) . '</div>';
            }

            // Show snippet when FTS search is active, otherwise excerpt
            $bodyText = $dto->snippet !== null
                ? '<p class="card__snippet">' . $dto->snippet . '</p>'
                : '<p class="card__excerpt">' . $this->h($dto->excerpt) . '</p>';

            $items .= <<<HTML
                <article class="card card--interactive" data-article-id="{$dto->id}">
                    <a href="/articles/{$dto->slug}" class="card__link">
                        {$imageHtml}
                        <div class="card__body">
                            <h2 class="card__title">{$this->h($dto->title)}</h2>
                            {$bodyText}
                            <div class="card__meta">
                                <span class="card__date">{$this->formatDate($dto->createdAt)}</span>
                            </div>
                            {$tagsHtml}
                        </div>
                    </a>
                </article>
            HTML;
        }

        // Determine total for pagination — compute intersection of all active filters
        $allIds = null;
        if ($searchQuery !== '') {
            $allIds = $this->searchArticlesByFts($searchQuery)['ids'];
        }
        if ($tagSlug !== '') {
            $tagIds = $this->getArticleIdsByTagSlug($tagSlug);
            $allIds = $allIds !== null ? array_values(array_intersect($allIds, $tagIds)) : $tagIds;
        }
        if ($categoryName !== '') {
            $catId = $this->findCategoryId($categoryName);
            $catIds = $catId !== null ? $this->getArticleIdsByCategoryId($catId) : [];
            $allIds = $allIds !== null ? array_values(array_intersect($allIds, $catIds)) : $catIds;
        }
        $total = $allIds !== null ? count($allIds) : $this->service->countPublished();

        $hasMore = ($page * self::PER_PAGE) < $total;

        $loadMore = '';
        if ($hasMore) {
            $nextPage = $page + 1;
            $queryParams = "page={$nextPage}";
            if ($searchQuery !== '') {
                $queryParams .= '&q=' . urlencode($searchQuery);
            }
            if ($tagSlug !== '') {
                $queryParams .= '&tag=' . urlencode($tagSlug);
            }
            if ($categoryName !== '') {
                $queryParams .= '&category=' . urlencode($categoryName);
            }
            $loadMore = <<<HTML
                <div class="load-more-container">
                    <button
                        class="btn btn--secondary load-more-btn"
                        hx-get="/articles/load?{$queryParams}"
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
                                </circle>
                            </svg>
                        </span>
                    </button>
                </div>
            HTML;
        }

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

    /**
     * Filter articles by tag slug.
     */
    private function filterByTag(string $tagSlug, int $page, string $categoryName = ''): Response
    {
        $allPublished = $this->service->findPublished(limit: 100, offset: 0);

        $filtered = [];
        foreach ($allPublished as $article) {
            $dto = $this->attachTagsToDto(ArticleDTO::fromEntity($article));
            foreach ($dto->tags as $tag) {
                if ($tag['slug'] === $tagSlug) {
                    $filtered[] = $dto;
                    break;
                }
            }
        }

        // If category filter is also active, narrow to category
        if ($categoryName !== '' && $filtered !== []) {
            $catId = $this->findCategoryId($categoryName);
            if ($catId !== null) {
                $filtered = array_values(array_filter(
                    $filtered,
                    fn (ArticleDTO $a): bool => $a->categoryId === $catId,
                ));
            } else {
                $filtered = [];
            }
        }

        $articles = array_slice($filtered, ($page - 1) * self::PER_PAGE, self::PER_PAGE);
        $total = count($filtered);
        $hasMore = ($page * self::PER_PAGE) < $total;

        return $this->view->render('pages/articles/index', [
            'title' => 'Articles tagged with "' . $tagSlug . '"',
            'currentPage' => 'articles',
            'message' => 'Filtered by tag: ' . $tagSlug,
            'articles' => $articles,
            'searchQuery' => '',
            'tagSlug' => $tagSlug,
            'categoryName' => $categoryName,
            'allTags' => $this->fetchAllTags(),
            'allCategories' => $this->fetchAllCategories(),
            'pagination' => [
                'limit' => self::PER_PAGE,
                'page' => $page,
                'has_more' => $hasMore,
                'total' => $total,
            ],
        ]);
    }

    /**
     * Filter articles by category name.
     */
    private function filterByCategory(string $categoryName, int $page): Response
    {
        $catId = $this->findCategoryId($categoryName);

        if ($catId === null) {
            return $this->view->render('pages/articles/index', [
                'title' => 'Category not found',
                'currentPage' => 'articles',
                'message' => 'No articles found in category: ' . $categoryName,
                'articles' => [],
                'searchQuery' => '',
                'tagSlug' => '',
                'categoryName' => $categoryName,
                'allTags' => $this->fetchAllTags(),
                'allCategories' => $this->fetchAllCategories(),
                'pagination' => [
                    'limit' => self::PER_PAGE,
                    'page' => $page,
                    'has_more' => false,
                    'total' => 0,
                ],
            ]);
        }

        $allPublished = $this->service->findPublished(limit: 100, offset: 0);

        $filtered = array_values(array_filter(
            $allPublished,
            fn (object $a): bool => $a->categoryId === $catId,
        ));

        $dtoArticles = array_map(
            fn (object $a): ArticleDTO => $this->attachTagsToDto(ArticleDTO::fromEntity($a)),
            $filtered
        );

        $articles = array_slice($dtoArticles, ($page - 1) * self::PER_PAGE, self::PER_PAGE);
        $total = count($dtoArticles);
        $hasMore = ($page * self::PER_PAGE) < $total;

        return $this->view->render('pages/articles/index', [
            'title' => $categoryName,
            'currentPage' => 'articles',
            'message' => 'Articles in category: ' . $categoryName,
            'articles' => $articles,
            'searchQuery' => '',
            'tagSlug' => '',
            'categoryName' => $categoryName,
            'allTags' => $this->fetchAllTags(),
            'allCategories' => $this->fetchAllCategories(),
            'pagination' => [
                'limit' => self::PER_PAGE,
                'page' => $page,
                'has_more' => $hasMore,
                'total' => $total,
            ],
        ]);
    }

    // ---- Category helpers ----

    /**
     * Find category ID by category name.
     */
    private function findCategoryId(string $categoryName): ?int
    {
        $rows = $this->getDbConnection()->query(
            'SELECT id FROM "categories" WHERE name = ?',
            [$categoryName],
        );

        if ($rows === [] || !isset($rows[0]['id']) || !is_numeric($rows[0]['id'])) {
            return null;
        }

        return (int) $rows[0]['id'];
    }

    /**
     * Get article IDs that belong to a category.
     *
     * @return array<int>
     */
    private function getArticleIdsByCategoryId(int $categoryId): array
    {
        $rows = $this->getDbConnection()->query(
            'SELECT id FROM "articles" WHERE "category_id" = ?',
            [$categoryId],
        );
        return array_map(fn (array $row): int => is_numeric($row['id'] ?? null) ? (int) $row['id'] : 0, $rows);
    }

    // ---- Tag helpers ----

    /**
     * Fetch all tags with article count (for tag cloud).
     *
     * @return array<int, array{id: int, name: string, slug: string, article_count: int}>
     */
    private function fetchAllTags(): array
    {
        /** @var array<int, array{id: int, name: string, slug: string, article_count: int}> $results */
        $results = $this->getDbConnection()->query(
            'SELECT t.*, (SELECT COUNT(*) FROM article_tags WHERE tag_id = t.id) as article_count FROM "tags" t ORDER BY "article_count" DESC, "name" ASC'
        );
        return $results;
    }

    /**
     * Fetch all categories with article count (for category cloud).
     *
     * @return array<int, array{id: int, name: string, slug: string, article_count: int}>
     */
    private function fetchAllCategories(): array
    {
        /** @var array<int, array{id: int, name: string, slug: string, article_count: int}> $results */
        $results = $this->getDbConnection()->query(
            'SELECT c.*, (SELECT COUNT(*) FROM articles WHERE category_id = c.id AND published = \'1\') as article_count FROM "categories" c ORDER BY "article_count" DESC, "name" ASC'
        );
        return $results;
    }

    /**
     * Fetch tags for an article and attach them to the DTO via constructor copy.
     */
    private function attachTagsToDto(ArticleDTO $dto): ArticleDTO
    {
        if ($dto->id === null) {
            return $dto;
        }

        $tags = $this->fetchTagsForArticle($dto->id);
        $categoryName = $this->fetchCategoryName($dto->categoryId);

        return new ArticleDTO(
            id: $dto->id,
            title: $dto->title,
            content: $dto->content,
            slug: $dto->slug,
            excerpt: $dto->excerpt,
            image: $dto->image,
            published: $dto->published,
            status: $dto->status,
            categoryId: $dto->categoryId,
            createdAt: $dto->createdAt,
            categoryName: $categoryName,
            tags: $tags,
        );
    }

    /**
     * Fetch category name by category ID.
     */
    private function fetchCategoryName(?int $categoryId): ?string
    {
        if ($categoryId === null) {
            return null;
        }

        $rows = $this->getDbConnection()->query(
            'SELECT name FROM "categories" WHERE id = ?',
            [$categoryId],
        );

        if ($rows === [] || !isset($rows[0]['name']) || !is_string($rows[0]['name'])) {
            return null;
        }

        return $rows[0]['name'];
    }

    /**
     * Fetch tags assigned to a given article.
     *
     * @return array<int, array{id: int, name: string, slug: string}>
     */
    private function fetchTagsForArticle(int $articleId): array
    {
        /** @var array<int, array{id: int, name: string, slug: string}> $results */
        $results = $this->getDbConnection()->query(
            'SELECT t.* FROM "tags" t INNER JOIN "article_tags" at ON t.id = at.tag_id WHERE at.article_id = ? ORDER BY t.name',
            [$articleId],
        );
        return $results;
    }

    // ---- FTS search ----

    /**
     * Search articles using SQLite FTS4 full-text index.
     *
     * @return array{ids: array<int, int>, snippets: array<int, string|null>}
     */
    private function searchArticlesByFts(string $query): array
    {
        $safe = preg_replace('/[^\w\s\-]/u', '', $query);
        $safe = trim($safe ?? '');

        if ($safe === '') {
            return ['ids' => [], 'snippets' => []];
        }

        $terms = array_values(array_filter(
            explode(' ', $safe),
            fn (string $t): bool => $t !== '',
        ));

        if ($terms === []) {
            return ['ids' => [], 'snippets' => []];
        }

        $ftsQuery = implode('* ', $terms) . '*';

        $rows = $this->getDbConnection()->query(
            "SELECT docid, snippet(articles_fts, '<mark>', '</mark>', '…', -1, 32) as snippet
             FROM articles_fts
             WHERE articles_fts MATCH ?
             ORDER BY docid",
            [$ftsQuery],
        );

        $ids = [];
        $snippets = [];
        foreach ($rows as $row) {
            $id = is_numeric($row['docid'] ?? null) ? (int) $row['docid'] : 0;
            $ids[] = $id;
            $raw = (isset($row['snippet']) && is_string($row['snippet'])) ? $row['snippet'] : null;
            if ($raw !== null) {
                // Escape all HTML, then restore only safe <mark> tags
                $escaped = htmlspecialchars($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $snippets[$id] = str_replace(
                    ['&lt;mark&gt;', '&lt;/mark&gt;'],
                    ['<mark>', '</mark>'],
                    $escaped,
                );
            } else {
                $snippets[$id] = null;
            }
        }

        return ['ids' => $ids, 'snippets' => $snippets];
    }

    private function getDbConnection(): ConnectionInterface
    {
        return $this->blogConnection->getConnection();
    }
}
