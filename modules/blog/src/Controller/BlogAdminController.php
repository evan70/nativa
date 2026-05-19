<?php

declare(strict_types=1);

namespace App\Blog\Controller;

use App\Blog\Database\BlogConnection;
use App\Blog\Entity\Article;
use App\Blog\Repository\ArticleRepository;
use App\Middleware\AdminAuthMiddleware;
use Marko\Admin\Contracts\AdminSectionRegistryInterface;
use Marko\Authentication\Contracts\GuardInterface;
use Marko\Database\Connection\ConnectionInterface;
use Marko\Session\Contracts\SessionInterface;
use Marko\Routing\Attributes\Delete;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Middleware;
use Marko\Routing\Attributes\Post;
use Marko\Routing\Attributes\Put;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;

#[Middleware(AdminAuthMiddleware::class)]
class BlogAdminController
{
    /** @var array<string, string> */
    private array $errors = [];

    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly BlogConnection $blogConnection,
        private readonly ViewInterface $view,
        private readonly SessionInterface $session,
        private readonly AdminSectionRegistryInterface $sectionRegistry,
        private readonly GuardInterface $guard,
    ) {}

    /**
     * Admin list of articles.
     */
    #[Get(path: '/mark/articles')]
    public function index(): Response
    {
        $articles = $this->articleRepository->findAll();

        // Attach tags to each article
        foreach ($articles as $article) {
            $article->tags = $this->fetchTagsForArticle((int) $article->id);
        }

        // Handle title search via SQLite FTS4
        $searchQuery = trim($_GET['q'] ?? '');
        if ($searchQuery !== '') {
            $matchingIds = $this->searchArticlesByFts($searchQuery);
            $articles = array_values(array_filter($articles, function (Article $article) use ($matchingIds): bool {
                return isset($article->id) && in_array((int) $article->id, $matchingIds, true);
            }));
        }

        // Handle tag filter
        $selectedTagId = 0;
        $tagFilterInput = $_GET['tag_id'] ?? '';
        if (is_numeric($tagFilterInput) && (int) $tagFilterInput > 0) {
            $selectedTagId = (int) $tagFilterInput;
            $articles = array_values(array_filter($articles, function (Article $article) use ($selectedTagId): bool {
                if (!isset($article->tags) || !is_array($article->tags)) {
                    return false;
                }
                foreach ($article->tags as $tag) {
                    if (isset($tag['id']) && (int) $tag['id'] === $selectedTagId) {
                        return true;
                    }
                }
                return false;
            }));
        }

        $allTags = $this->fetchAllTags();

        $logParts = ['showing ' . count($articles) . ' articles'];
        if ($searchQuery !== '') {
            $logParts[] = "search: '$searchQuery'";
        }
        if ($selectedTagId > 0) {
            $logParts[] = 'filtered by tag #' . $selectedTagId;
        }
        error_log('[BlogAdmin] Index - ' . implode(', ', $logParts));

        return $this->view->render('pages/mark/articles', [
            'title' => ($searchQuery !== '' || $selectedTagId > 0)
                ? 'Articles filtered'
                : 'Articles Administration',
            'articles' => $articles,
            'allTags' => $allTags,
            'selectedTagId' => $selectedTagId,
            'searchQuery' => $searchQuery,
            'flashMessages' => $this->session->flash()->all(),
            'menuItems' => $this->buildMenuItems('articles'),
            'currentUser' => $this->getCurrentUser(),
            'activeSection' => 'articles',
        ]);
    }

    /**
     * Form to create a new article.
     */
    #[Get(path: '/mark/articles/new')]
    public function create(): Response
    {
        error_log('[BlogAdmin] Create form accessed');

        $allTags = $this->fetchAllTags();

        return $this->view->render('pages/mark/article-form', [
            'title' => 'Create New Article',
            'article' => null,
            'categories' => [],
            'allTags' => $allTags,
            'selectedTagIds' => [],
            'errors' => $this->errors,
            'flashMessages' => $this->session->flash()->all(),
            'menuItems' => $this->buildMenuItems('articles'),
            'currentUser' => $this->getCurrentUser(),
            'activeSection' => 'articles',
        ]);
    }

    /**
     * Form to edit an existing article.
     */
    #[Get(path: '/mark/articles/{id}/edit')]
    public function edit(int $id): Response
    {
        $article = $this->articleRepository->find($id);

        if ($article === null) {
            error_log('[BlogAdmin] Edit - article not found: ' . $id);
            return Response::html($this->view->renderToString('pages/errors/404', [
                'title' => 'Article Not Found',
            ]), 404);
        }

        $allTags = $this->fetchAllTags();
        $selectedTagIds = array_map(
            fn (array $tag): int => $tag['id'],
            $this->fetchTagsForArticle($id),
        );

        error_log('[BlogAdmin] Edit form for article: ' . $id);

        return $this->view->render('pages/mark/article-form', [
            'title' => 'Edit Article',
            'article' => $article,
            'categories' => [],
            'allTags' => $allTags,
            'selectedTagIds' => $selectedTagIds,
            'errors' => $this->errors,
            'flashMessages' => $this->session->flash()->all(),
            'menuItems' => $this->buildMenuItems('articles'),
            'currentUser' => $this->getCurrentUser(),
            'activeSection' => 'articles',
        ]);
    }

    /**
     * Store a new article (POST).
     */
    #[Post(path: '/mark/articles')]
    public function store(Request $request): Response
    {
        $post = $request->post();
        
        $title = $this->getString($post, 'title');
        $slug = $this->getString($post, 'slug');
        $excerpt = $this->getString($post, 'excerpt');
        $content = $this->getString($post, 'content');
        $image = $this->getString($post, 'image');
        $status = $this->getString($post, 'status', 'draft');
        $categoryId = $this->getString($post, 'category_id');
        $published = $this->getBool($post, 'published');

        // Validation
        $this->errors = [];
        if (empty(trim($title))) {
            $this->errors['title'] = 'Title is required';
        }
        if (empty(trim($content))) {
            $this->errors['content'] = 'Content is required';
        }

        if ($this->errors !== []) {
            error_log('[BlogAdmin] Store - validation errors: ' . json_encode($this->errors));
            return $this->view->render('pages/mark/article-form', [
                'title' => 'Create New Article',
                'article' => null,
                'categories' => [],
                'allTags' => $this->fetchAllTags(),
                'selectedTagIds' => $this->getArrayInt($post, 'tags'),
                'errors' => $this->errors,
                'menuItems' => $this->buildMenuItems('articles'),
                'currentUser' => $this->getCurrentUser(),
                'activeSection' => 'articles',
            ]);
        }

        // Auto-generate slug if empty
        if (empty(trim($slug))) {
            $slug = $this->generateSlug($title);
            error_log('[BlogAdmin] Auto-generated slug: ' . $slug);
        }

        $article = new Article();
        $article->title = trim($title);
        $article->slug = trim($slug);
        $article->excerpt = trim($excerpt);
        $article->content = trim($content);
        $article->image = trim($image);
        $article->status = $status;
        $article->categoryId = $this->getIntOrNull($categoryId);
        $article->published = $published;
        $article->createdAt = date('Y-m-d H:i:s');

        $this->articleRepository->save($article);

        // Sync article tags
        $tagIds = $this->getArrayInt($post, 'tags');
        $this->syncArticleTags((int) $article->id, $tagIds);

        // Sync FTS index
        $this->syncFtsForArticle(
            (int) $article->id,
            $article->title,
            $article->excerpt,
            $article->content,
        );

        $this->session->flash()->add('success', 'Article created successfully.');

        error_log('[BlogAdmin] Article created: ' . $article->id . ' - ' . $article->title . ' with ' . count($tagIds) . ' tags');

        return new Response('', 302, ['Location' => '/mark/articles']);
    }

    /**
     * Update an existing article (PUT).
     */
    #[Put(path: '/mark/articles/{id}')]
    public function update(int $id, Request $request): Response
    {
        $article = $this->articleRepository->find($id);
        
        if ($article === null) {
            error_log('[BlogAdmin] Update - article not found: ' . $id);
            return new Response('Article not found', 404);
        }

        $post = $request->post();
        
        $title = $this->getString($post, 'title');
        $slug = $this->getString($post, 'slug');
        $excerpt = $this->getString($post, 'excerpt');
        $content = $this->getString($post, 'content');
        $image = $this->getString($post, 'image');
        $status = $this->getString($post, 'status', 'draft');
        $categoryId = $this->getString($post, 'category_id');
        $published = $this->getBool($post, 'published');

        // Validation
        $this->errors = [];
        if (empty(trim($title))) {
            $this->errors['title'] = 'Title is required';
        }
        if (empty(trim($content))) {
            $this->errors['content'] = 'Content is required';
        }

        if ($this->errors !== []) {
            error_log('[BlogAdmin] Update - validation errors: ' . json_encode($this->errors));
            return $this->view->render('pages/mark/article-form', [
                'title' => 'Edit Article',
                'article' => $article,
                'categories' => [],
                'allTags' => $this->fetchAllTags(),
                'selectedTagIds' => $this->getArrayInt($post, 'tags'),
                'errors' => $this->errors,
                'menuItems' => $this->buildMenuItems('articles'),
                'currentUser' => $this->getCurrentUser(),
                'activeSection' => 'articles',
            ]);
        }

        // Auto-generate slug if empty
        if (empty(trim($slug))) {
            $slug = $this->generateSlug($title);
            error_log('[BlogAdmin] Auto-generated slug: ' . $slug);
        }

        $article->title = trim($title);
        $article->slug = trim($slug);
        $article->excerpt = trim($excerpt);
        $article->content = trim($content);
        $article->image = trim($image);
        $article->status = $status;
        $article->categoryId = $this->getIntOrNull($categoryId);
        $article->published = $published;

        $this->articleRepository->save($article);

        // Sync article tags
        $tagIds = $this->getArrayInt($post, 'tags');
        $this->syncArticleTags($id, $tagIds);

        // Sync FTS index
        $this->syncFtsForArticle($id, $article->title, $article->excerpt, $article->content);

        $this->session->flash()->add('success', 'Article updated successfully.');

        error_log('[BlogAdmin] Article updated: ' . $id . ' - ' . $article->title . ' with ' . count($tagIds) . ' tags');

        return new Response('', 302, ['Location' => '/mark/articles']);
    }

    /**
     * Delete an article (DELETE).
     */
    #[Delete(path: '/mark/articles/{id}')]
    public function delete(int $id): Response
    {
        $article = $this->articleRepository->find($id);

        if ($article === null) {
            error_log('[BlogAdmin] Delete - article not found: ' . $id);
            return new Response('Article not found', 404);
        }

        $title = $article->title;

        // Delete article_tags first
        $this->getDbConnection()->execute('DELETE FROM "article_tags" WHERE "article_id" = ?', [$id]);

        // Delete FTS index entry
        $this->getDbConnection()->execute('DELETE FROM articles_fts WHERE docid = ?', [$id]);

        $this->articleRepository->delete($article);

        $this->session->flash()->add('success', 'Article deleted successfully.');

        error_log('[BlogAdmin] Article deleted: ' . $id . ' - ' . $title);

        return new Response('', 302, ['Location' => '/mark/articles']);
    }

    /**
     * Get a string value from POST data with proper type handling.
     *
     * @param array<string, mixed> $post
     */
    private function getString(array $post, string $key, string $default = ''): string
    {
        $value = $post[$key] ?? $default;
        
        if (is_string($value)) {
            return $value;
        }
        
        if (is_numeric($value)) {
            return (string) $value;
        }
        
        return $default;
    }

    /**
     * Get a boolean value from POST data.
     *
     * @param array<string, mixed> $post
     */
    private function getBool(array $post, string $key, bool $default = false): bool
    {
        $value = $post[$key] ?? null;
        
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            return $value === '1' || $value === 'true';
        }
        
        if (is_int($value)) {
            return $value === 1;
        }
        
        return $default;
    }

    /**
     * Get an integer or null from POST data.
     */
    private function getIntOrNull(string $value): ?int
    {
        if ($value === '') {
            return null;
        }
        
        if (is_numeric($value)) {
            return (int) $value;
        }
        
        return null;
    }

    /**
     * Generate a URL-friendly slug from title.
     */
    private function generateSlug(string $title): string
    {
        // Convert to lowercase and replace spaces with hyphens
        $slug = strtolower($title);
        $cleaned = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $withDashes = preg_replace('/[\s-]+/', '-', $cleaned ?? '');
        return trim($withDashes ?? '', '-');
    }

    // ---- Tag helpers ----

    /**
     * Get an array of integers from POST data.
     *
     * @param array<string, mixed> $post
     * @return array<int, int>
     */
    private function getArrayInt(array $post, string $key): array
    {
        $values = $post[$key] ?? [];
        if (!is_array($values)) {
            return [];
        }

        $result = [];
        foreach ($values as $v) {
            if (is_numeric($v)) {
                $result[] = (int) $v;
            }
        }
        return $result;
    }

    /**
     * Sync article_tags for a given article.
     *
     * @param array<int, int> $tagIds
     */
    private function syncArticleTags(int $articleId, array $tagIds): void
    {
        $db = $this->getDbConnection();

        // Delete all existing tags for this article
        $db->execute('DELETE FROM "article_tags" WHERE "article_id" = ?', [$articleId]);

        // Insert new tags
        foreach ($tagIds as $tagId) {
            $db->execute(
                'INSERT INTO "article_tags" ("article_id", "tag_id") VALUES (?, ?)',
                [$articleId, $tagId],
            );
        }

        error_log('[BlogAdmin] Synced ' . count($tagIds) . ' tags for article #' . $articleId);
    }

    /**
     * Fetch all tags from the database.
     *
     * @return array<int, array{id: int, name: string, slug: string}>
     */
    private function fetchAllTags(): array
    {
        return $this->getDbConnection()->query('SELECT * FROM "tags" ORDER BY "name"');
    }

    /**
     * Fetch tags assigned to a given article.
     *
     * @return array<int, array{id: int, name: string, slug: string}>
     */
    private function fetchTagsForArticle(int $articleId): array
    {
        return $this->getDbConnection()->query(
            'SELECT t.* FROM "tags" t INNER JOIN "article_tags" at ON t.id = at.tag_id WHERE at.article_id = ? ORDER BY t.name',
            [$articleId],
        );
    }

    /**
     * Search articles using SQLite FTS4 full-text index.
     *
     * @return array<int, int> Matching article IDs.
     */
    private function searchArticlesByFts(string $query): array
    {
        // Sanitise FTS query — strip special chars, keep alphanumeric + spaces + hyphens
        $safe = preg_replace('/[^\w\s\-]/u', '', $query);
        $safe = trim($safe ?? '');

        if ($safe === '') {
            return [];
        }

        // Build prefix query: each word becomes a prefix term, filter empty segments
        $terms = array_values(array_filter(
            explode(' ', $safe),
            fn (string $t): bool => $t !== '',
        ));

        if ($terms === []) {
            return [];
        }

        $ftsQuery = implode('* ', $terms) . '*';

        $rows = $this->getDbConnection()->query(
            'SELECT docid FROM articles_fts WHERE articles_fts MATCH ?',
            [$ftsQuery],
        );

        return array_map(fn (array $row): int => (int) $row['docid'], $rows);
    }

    /**
     * Sync a single article's FTS index entry.
     * Deletes and re-inserts the row to match current article data.
     */
    private function syncFtsForArticle(int $articleId, string $title, string $excerpt, string $content): void
    {
        $db = $this->getDbConnection();
        $db->execute('DELETE FROM articles_fts WHERE docid = ?', [$articleId]);
        $db->execute(
            'INSERT INTO articles_fts(docid, title, excerpt, content) VALUES (?, ?, ?, ?)',
            [$articleId, $title, $excerpt, $content],
        );
    }

    private function getDbConnection(): ConnectionInterface
    {
        return $this->blogConnection->getConnection();
    }

    /**
     * @return array<int, array{url: string, label: string, icon: string, active: bool}>
     */
    private function buildMenuItems(string $currentSlug = ''): array
    {
        $items = [];
        foreach ($this->sectionRegistry->all() as $section) {
            $slug = $section->getSlug();
            $items[] = [
                'url' => '/mark' . ($slug !== 'dashboard' ? '/' . $slug : ''),
                'label' => $section->getLabel(),
                'icon' => $section->getIcon(),
                'active' => $slug === $currentSlug,
            ];
        }
        return $items;
    }

    /**
     * Get current authenticated user for the view.
     */
    private function getCurrentUser(): ?\Marko\Authentication\UserInterface
    {
        return $this->guard->user();
    }
}