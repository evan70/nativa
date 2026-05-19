<?php

declare(strict_types=1);

namespace App\Portfolio\Controller;

use App\Middleware\AdminAuthMiddleware;
use App\Portfolio\Database\PortfolioConnection;
use App\Portfolio\Entity\PortfolioItem;
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
class PortfolioAdminController
{
    /** @var array<string, string> */
    private array $errors = [];

    public function __construct(
        private readonly PortfolioConnection $portfolioConnection,
        private readonly ViewInterface $view,
        private readonly SessionInterface $session,
        private readonly AdminSectionRegistryInterface $sectionRegistry,
        private readonly GuardInterface $guard,
    ) {}

    /**
     * Admin list of portfolio items.
     */
    #[Get(path: '/admin/portfolio/items')]
    public function index(): Response
    {
        $items = $this->fetchAllItems();

        error_log('[PortfolioAdmin] Index - showing ' . count($items) . ' items');

        return $this->view->render('pages/mark/admin-portfolio-items', [
            'title' => 'Portfolio Items',
            'items' => $items,
            'flashMessages' => $this->session->flash()->all(),
            'menuItems' => $this->buildMenuItems('portfolio'),
            'currentUser' => $this->getCurrentUser(),
            'activeSection' => 'portfolio',
        ]);
    }

    /**
     * Form to create a new portfolio item.
     */
    #[Get(path: '/admin/portfolio/items/new')]
    public function create(): Response
    {
        error_log('[PortfolioAdmin] Create form accessed');

        return $this->view->render('pages/mark/portfolio-item-form', [
            'title' => 'Create New Portfolio Item',
            'item' => null,
            'errors' => $this->errors,
            'flashMessages' => $this->session->flash()->all(),
            'menuItems' => $this->buildMenuItems('portfolio'),
            'currentUser' => $this->getCurrentUser(),
            'activeSection' => 'portfolio',
        ]);
    }

    /**
     * Form to edit an existing portfolio item.
     */
    #[Get(path: '/admin/portfolio/items/{id}/edit')]
    public function edit(int $id): Response
    {
        $item = $this->findItem($id);

        if ($item === null) {
            error_log('[PortfolioAdmin] Edit - item not found: ' . $id);
            return Response::html($this->view->renderToString('pages/errors/404', [
                'title' => 'Portfolio Item Not Found',
            ]), 404);
        }

        error_log('[PortfolioAdmin] Edit form for item: ' . $id);

        return $this->view->render('pages/mark/portfolio-item-form', [
            'title' => 'Edit Portfolio Item',
            'item' => $item,
            'errors' => $this->errors,
            'flashMessages' => $this->session->flash()->all(),
            'menuItems' => $this->buildMenuItems('portfolio'),
            'currentUser' => $this->getCurrentUser(),
            'activeSection' => 'portfolio',
        ]);
    }

    /**
     * Store a new portfolio item (POST).
     */
    #[Post(path: '/admin/portfolio/items')]
    public function store(Request $request): Response
    {
        $title = trim((string) $request->post('title', ''));
        $slug = trim((string) $request->post('slug', ''));
        $subtitle = trim((string) $request->post('subtitle', ''));
        $description = trim((string) $request->post('description', ''));
        $category = trim((string) $request->post('category', ''));
        $role = trim((string) $request->post('role', ''));
        $year = trim((string) $request->post('year', ''));
        $stack = trim((string) $request->post('stack', ''));
        $image = trim((string) $request->post('image', ''));
        $displayOrder = (int) $request->post('display_order', '0');

        // Validation
        $this->errors = [];
        if ($title === '') {
            $this->errors['title'] = 'Title is required';
        }
        if ($slug === '') {
            $slug = $this->generateSlug($title);
        }
        if ($description === '') {
            $this->errors['description'] = 'Description is required';
        }

        // Check slug uniqueness
        if ($this->errors === [] && $this->slugExists($slug)) {
            $this->errors['slug'] = 'An item with this slug already exists';
        }

        if ($this->errors !== []) {
            error_log('[PortfolioAdmin] Store - validation errors: ' . json_encode($this->errors));
            return $this->view->render('pages/mark/portfolio-item-form', [
                'title' => 'Create New Portfolio Item',
                'item' => null,
                'errors' => $this->errors,
                'menuItems' => $this->buildMenuItems('portfolio'),
                'currentUser' => $this->getCurrentUser(),
                'activeSection' => 'portfolio',
            ]);
        }

        $this->getConnection()->execute(
            'INSERT INTO "portfolio_items" ("title", "slug", "subtitle", "description", "category", "role", "year", "stack", "image", "display_order") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$title, $slug, $subtitle, $description, $category, $role, $year, $stack, $image, $displayOrder],
        );

        $this->session->flash()->add('success', 'Portfolio item "' . $title . '" created successfully.');

        error_log('[PortfolioAdmin] Item created: ' . $title . ' (slug: ' . $slug . ')');

        return new Response('', 302, ['Location' => '/admin/portfolio/items']);
    }

    /**
     * Update an existing portfolio item (PUT).
     */
    #[Put(path: '/admin/portfolio/items/{id}')]
    public function update(int $id, Request $request): Response
    {
        $item = $this->findItem($id);

        if ($item === null) {
            error_log('[PortfolioAdmin] Update - item not found: ' . $id);
            return new Response('Item not found', 404);
        }

        $title = trim((string) $request->post('title', ''));
        $slug = trim((string) $request->post('slug', ''));
        $subtitle = trim((string) $request->post('subtitle', ''));
        $description = trim((string) $request->post('description', ''));
        $category = trim((string) $request->post('category', ''));
        $role = trim((string) $request->post('role', ''));
        $year = trim((string) $request->post('year', ''));
        $stack = trim((string) $request->post('stack', ''));
        $image = trim((string) $request->post('image', ''));
        $displayOrder = (int) $request->post('display_order', '0');

        // Validation
        $this->errors = [];
        if ($title === '') {
            $this->errors['title'] = 'Title is required';
        }
        if ($slug === '') {
            $slug = $this->generateSlug($title);
        }
        if ($description === '') {
            $this->errors['description'] = 'Description is required';
        }

        // Check slug uniqueness (exclude current item)
        if ($this->errors === [] && $slug !== $item['slug'] && $this->slugExists($slug)) {
            $this->errors['slug'] = 'An item with this slug already exists';
        }

        if ($this->errors !== []) {
            error_log('[PortfolioAdmin] Update - validation errors: ' . json_encode($this->errors));
            return $this->view->render('pages/mark/portfolio-item-form', [
                'title' => 'Edit Portfolio Item',
                'item' => $item,
                'errors' => $this->errors,
                'menuItems' => $this->buildMenuItems('portfolio'),
                'currentUser' => $this->getCurrentUser(),
                'activeSection' => 'portfolio',
            ]);
        }

        $this->getConnection()->execute(
            'UPDATE "portfolio_items" SET "title" = ?, "slug" = ?, "subtitle" = ?, "description" = ?, "category" = ?, "role" = ?, "year" = ?, "stack" = ?, "image" = ?, "display_order" = ? WHERE "id" = ?',
            [$title, $slug, $subtitle, $description, $category, $role, $year, $stack, $image, $displayOrder, $id],
        );

        $this->session->flash()->add('success', 'Portfolio item "' . $title . '" updated successfully.');

        error_log('[PortfolioAdmin] Item updated: id=' . $id . ', title=' . $title);

        return new Response('', 302, ['Location' => '/admin/portfolio/items']);
    }

    /**
     * Delete a portfolio item (DELETE).
     */
    #[Delete(path: '/admin/portfolio/items/{id}')]
    public function delete(int $id): Response
    {
        $item = $this->findItem($id);

        if ($item === null) {
            error_log('[PortfolioAdmin] Delete - item not found: ' . $id);
            return new Response('Item not found', 404);
        }

        $this->getConnection()->execute(
            'DELETE FROM "portfolio_items" WHERE "id" = ?',
            [$id],
        );

        $this->session->flash()->add('success', 'Portfolio item "' . $item['title'] . '" deleted successfully.');

        error_log('[PortfolioAdmin] Item deleted: id=' . $id . ', title=' . $item['title']);

        return new Response('', 302, ['Location' => '/admin/portfolio/items']);
    }

    /**
     * Fetch all portfolio items from the database.
     *
     * @return array<array{id: int, title: string, slug: string, subtitle: string, description: string, category: string, role: string, year: string, stack: string, image: string, display_order: int}>
     */
    private function fetchAllItems(): array
    {
        return $this->getConnection()->query(
            'SELECT * FROM "portfolio_items" ORDER BY "display_order", "title"',
        );
    }

    /**
     * Find a single portfolio item by ID.
     *
     * @return array{id: int, title: string, slug: string, subtitle: string, description: string, category: string, role: string, year: string, stack: string, image: string, display_order: int}|null
     */
    private function findItem(int $id): ?array
    {
        $results = $this->getConnection()->query(
            'SELECT * FROM "portfolio_items" WHERE "id" = ?',
            [$id],
        );
        return $results[0] ?? null;
    }

    /**
     * Check if an item with the given slug already exists.
     */
    private function slugExists(string $slug): bool
    {
        $results = $this->getConnection()->query(
            'SELECT COUNT(*) as cnt FROM "portfolio_items" WHERE "slug" = ?',
            [$slug],
        );
        return ($results[0]['cnt'] ?? 0) > 0;
    }

    private function getConnection(): \Marko\Database\Connection\ConnectionInterface
    {
        return $this->portfolioConnection->getConnection();
    }

    /**
     * Generate a URL-friendly slug from title.
     */
    private function generateSlug(string $title): string
    {
        $slug = strtolower($title);
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
