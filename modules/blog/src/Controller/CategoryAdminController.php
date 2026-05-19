<?php

declare(strict_types=1);

namespace App\Blog\Controller;

use App\Blog\Database\BlogConnection;
use App\Middleware\AdminAuthMiddleware;
use Marko\Admin\Contracts\AdminSectionRegistryInterface;
use Marko\Authentication\Contracts\GuardInterface;
use Marko\Routing\Attributes\Delete;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Middleware;
use Marko\Routing\Attributes\Post;
use Marko\Routing\Attributes\Put;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\Session\Contracts\SessionInterface;
use Marko\View\ViewInterface;

#[Middleware(AdminAuthMiddleware::class)]
class CategoryAdminController
{
    /** @var array<string, string> */
    private array $errors = [];

    public function __construct(
        private readonly BlogConnection $blogConnection,
        private readonly ViewInterface $view,
        private readonly SessionInterface $session,
        private readonly AdminSectionRegistryInterface $sectionRegistry,
        private readonly GuardInterface $guard,
    ) {}

    /**
     * Admin list of categories.
     */
    #[Get(path: '/mark/categories')]
    public function index(): Response
    {
        $categories = $this->fetchAllCategories();

        error_log('[CategoryAdmin] Index - showing ' . count($categories) . ' categories');

        return $this->view->render('pages/mark/admin-categories', [
            'title' => 'Categories',
            'categories' => $categories,
            'flashMessages' => $this->session->flash()->all(),
            'menuItems' => $this->buildMenuItems('categories'),
            'currentUser' => $this->getCurrentUser(),
            'activeSection' => 'categories',
        ]);
    }

    /**
     * Form to create a new category.
     */
    #[Get(path: '/mark/categories/new')]
    public function create(): Response
    {
        error_log('[CategoryAdmin] Create form accessed');

        return $this->view->render('pages/mark/category-form', [
            'title' => 'Create New Category',
            'category' => null,
            'errors' => $this->errors,
            'flashMessages' => $this->session->flash()->all(),
            'menuItems' => $this->buildMenuItems('categories'),
            'currentUser' => $this->getCurrentUser(),
            'activeSection' => 'categories',
        ]);
    }

    /**
     * Form to edit an existing category.
     */
    #[Get(path: '/mark/categories/{id}/edit')]
    public function edit(int $id): Response
    {
        $category = $this->findCategory($id);

        if ($category === null) {
            error_log('[CategoryAdmin] Edit - category not found: ' . $id);
            return Response::html($this->view->renderToString('pages/errors/404', [
                'title' => 'Category Not Found',
            ]), 404);
        }

        error_log('[CategoryAdmin] Edit form for category: ' . $id);

        return $this->view->render('pages/mark/category-form', [
            'title' => 'Edit Category',
            'category' => $category,
            'errors' => $this->errors,
            'flashMessages' => $this->session->flash()->all(),
            'menuItems' => $this->buildMenuItems('categories'),
            'currentUser' => $this->getCurrentUser(),
            'activeSection' => 'categories',
        ]);
    }

    /**
     * Store a new category (POST).
     */
    #[Post(path: '/mark/categories')]
    public function store(Request $request): Response
    {
        $name = trim((string) $request->post('name', ''));
        $slug = trim((string) $request->post('slug', ''));
        $description = trim((string) $request->post('description', ''));

        // Validation
        $this->errors = [];
        if ($name === '') {
            $this->errors['name'] = 'Name is required';
        }
        if ($slug === '') {
            $slug = $this->generateSlug($name);
        }

        // Check slug uniqueness
        if ($this->errors === [] && $this->slugExists($slug)) {
            $this->errors['slug'] = 'A category with this slug already exists';
        }

        if ($this->errors !== []) {
            error_log('[CategoryAdmin] Store - validation errors: ' . json_encode($this->errors));
            return $this->view->render('pages/mark/category-form', [
                'title' => 'Create New Category',
                'category' => null,
                'errors' => $this->errors,
                'menuItems' => $this->buildMenuItems('categories'),
                'currentUser' => $this->getCurrentUser(),
                'activeSection' => 'categories',
            ]);
        }

        $this->getConnection()->execute(
            'INSERT INTO "categories" ("name", "slug", "description") VALUES (?, ?, ?)',
            [$name, $slug, $description],
        );

        $this->session->flash()->add('success', 'Category "' . $name . '" created successfully.');

        error_log('[CategoryAdmin] Category created: ' . $name . ' (slug: ' . $slug . ')');

        return new Response('', 302, ['Location' => '/mark/categories']);
    }

    /**
     * Update an existing category (PUT).
     */
    #[Put(path: '/mark/categories/{id}')]
    public function update(int $id, Request $request): Response
    {
        $category = $this->findCategory($id);

        if ($category === null) {
            error_log('[CategoryAdmin] Update - category not found: ' . $id);
            return new Response('Category not found', 404);
        }

        $name = trim((string) $request->post('name', ''));
        $slug = trim((string) $request->post('slug', ''));
        $description = trim((string) $request->post('description', ''));

        // Validation
        $this->errors = [];
        if ($name === '') {
            $this->errors['name'] = 'Name is required';
        }
        if ($slug === '') {
            $slug = $this->generateSlug($name);
        }

        // Check slug uniqueness (exclude current category)
        if ($this->errors === [] && $slug !== $category['slug'] && $this->slugExists($slug)) {
            $this->errors['slug'] = 'A category with this slug already exists';
        }

        if ($this->errors !== []) {
            error_log('[CategoryAdmin] Update - validation errors: ' . json_encode($this->errors));
            return $this->view->render('pages/mark/category-form', [
                'title' => 'Edit Category',
                'category' => $category,
                'errors' => $this->errors,
                'menuItems' => $this->buildMenuItems('categories'),
                'currentUser' => $this->getCurrentUser(),
                'activeSection' => 'categories',
            ]);
        }

        $this->getConnection()->execute(
            'UPDATE "categories" SET "name" = ?, "slug" = ?, "description" = ? WHERE "id" = ?',
            [$name, $slug, $description, $id],
        );

        $this->session->flash()->add('success', 'Category "' . $name . '" updated successfully.');

        error_log('[CategoryAdmin] Category updated: id=' . $id . ', name=' . $name);

        return new Response('', 302, ['Location' => '/mark/categories']);
    }

    /**
     * Delete a category (DELETE).
     */
    #[Delete(path: '/mark/categories/{id}')]
    public function delete(int $id): Response
    {
        $category = $this->findCategory($id);

        if ($category === null) {
            error_log('[CategoryAdmin] Delete - category not found: ' . $id);
            return new Response('Category not found', 404);
        }

        // Check if any articles reference this category
        $articleCount = $this->getConnection()->query(
            'SELECT COUNT(*) as cnt FROM "articles" WHERE "category_id" = ?',
            [$id],
        );

        if (($articleCount[0]['cnt'] ?? 0) > 0) {
            $this->session->flash()->add('error', 'Cannot delete category "' . $category['name'] . '": ' . $articleCount[0]['cnt'] . ' article(s) reference it.');
            error_log('[CategoryAdmin] Delete blocked - category has ' . $articleCount[0]['cnt'] . ' articles');

            return new Response('', 302, ['Location' => '/mark/categories']);
        }

        $this->getConnection()->execute(
            'DELETE FROM "categories" WHERE "id" = ?',
            [$id],
        );

        $this->session->flash()->add('success', 'Category "' . $category['name'] . '" deleted successfully.');

        error_log('[CategoryAdmin] Category deleted: id=' . $id . ', name=' . $category['name']);

        return new Response('', 302, ['Location' => '/mark/categories']);
    }

    /**
     * Fetch all categories from the database with article count.
     *
     * @return array<array{id: int, name: string, slug: string, description: string, article_count: int}>
     */
    private function fetchAllCategories(): array
    {
        return $this->getConnection()->query(
            'SELECT c.*, (SELECT COUNT(*) FROM articles WHERE category_id = c.id) as article_count FROM "categories" c ORDER BY "name"',
        );
    }

    /**
     * Find a single category by ID.
     *
     * @return array{id: int, name: string, slug: string, description: string}|null
     */
    private function findCategory(int $id): ?array
    {
        $results = $this->getConnection()->query(
            'SELECT * FROM "categories" WHERE "id" = ?',
            [$id],
        );
        return $results[0] ?? null;
    }

    /**
     * Check if a category with the given slug already exists.
     */
    private function slugExists(string $slug): bool
    {
        $results = $this->getConnection()->query(
            'SELECT COUNT(*) as cnt FROM "categories" WHERE "slug" = ?',
            [$slug],
        );
        return ($results[0]['cnt'] ?? 0) > 0;
    }

    private function getConnection(): \Marko\Database\Connection\ConnectionInterface
    {
        return $this->blogConnection->getConnection();
    }

    /**
     * Generate a URL-friendly slug from name.
     */
    private function generateSlug(string $name): string
    {
        $slug = strtolower($name);
        $cleaned = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $withDashes = preg_replace('/[\s-]+/', '-', $cleaned ?? '');
        return trim($withDashes ?? '', '-');
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

    private function getCurrentUser(): ?\Marko\Authentication\UserInterface
    {
        return $this->guard->user();
    }
}
