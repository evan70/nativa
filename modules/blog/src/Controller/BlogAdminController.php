<?php

declare(strict_types=1);

namespace App\Blog\Controller;

use App\Blog\Entity\Article;
use App\Blog\Repository\ArticleRepository;
use App\Middleware\AdminAuthMiddleware;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Middleware;
use Marko\Routing\Attributes\Post;
use Marko\Routing\Attributes\Put;
use Marko\Routing\Attributes\Delete;
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
        private readonly ViewInterface $view,
    ) {}

    /**
     * Admin list of articles.
     */
    #[Get(path: '/mark/articles')]
    public function index(): Response
    {
        $articles = $this->articleRepository->findAll();
        
        error_log('[BlogAdmin] Index - showing ' . count($articles) . ' articles');
        
        return $this->view->render('pages/dash/admin-articles', [
            'title' => 'Articles Administration',
            'articles' => $articles,
        ]);
    }

    /**
     * Form to create a new article.
     */
    #[Get(path: '/mark/articles/new')]
    public function create(): Response
    {
        error_log('[BlogAdmin] Create form accessed');
        
        return $this->view->render('pages/dash/article-form', [
            'title' => 'Create New Article',
            'article' => null,
            'categories' => [],
            'errors' => $this->errors,
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
            return $this->view->render('pages/errors/404', [
                'title' => 'Article Not Found',
            ])->withStatus(404);
        }
        
        error_log('[BlogAdmin] Edit form for article: ' . $id);
        
        return $this->view->render('pages/dash/article-form', [
            'title' => 'Edit Article',
            'article' => $article,
            'categories' => [],
            'errors' => $this->errors,
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
            return $this->view->render('pages/dash/article-form', [
                'title' => 'Create New Article',
                'article' => null,
                'categories' => [],
                'errors' => $this->errors,
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

        error_log('[BlogAdmin] Article created: ' . $article->id . ' - ' . $article->title);

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
            return $this->view->render('pages/dash/article-form', [
                'title' => 'Edit Article',
                'article' => $article,
                'categories' => [],
                'errors' => $this->errors,
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

        error_log('[BlogAdmin] Article updated: ' . $id . ' - ' . $article->title);

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
        $this->articleRepository->delete($article);

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
}