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
class TagAdminController
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
     * Admin list of tags.
     */
    #[Get(path: '/mark/tags')]
    public function index(): Response
    {
        $tags = $this->fetchAllTags();

        error_log('[TagAdmin] Index - showing ' . count($tags) . ' tags');

        return $this->view->render('pages/mark/tags', [
            'title' => 'Tags',
            'tags' => $tags,
            'flashMessages' => $this->session->flash()->all(),
            'menuItems' => $this->buildMenuItems('tags'),
            'currentUser' => $this->getCurrentUser(),
            'activeSection' => 'tags',
        ]);
    }

    /**
     * Form to create a new tag.
     */
    #[Get(path: '/mark/tags/new')]
    public function create(): Response
    {
        error_log('[TagAdmin] Create form accessed');

        return $this->view->render('pages/mark/tag-form', [
            'title' => 'Create New Tag',
            'tag' => null,
            'errors' => $this->errors,
            'flashMessages' => $this->session->flash()->all(),
            'menuItems' => $this->buildMenuItems('tags'),
            'currentUser' => $this->getCurrentUser(),
            'activeSection' => 'tags',
        ]);
    }

    /**
     * Form to edit an existing tag.
     */
    #[Get(path: '/mark/tags/{id}/edit')]
    public function edit(int $id): Response
    {
        $tag = $this->findTag($id);

        if ($tag === null) {
            error_log('[TagAdmin] Edit - tag not found: ' . $id);
            return Response::html($this->view->renderToString('pages/errors/404', [
                'title' => 'Tag Not Found',
            ]), 404);
        }

        error_log('[TagAdmin] Edit form for tag: ' . $id);

        return $this->view->render('pages/mark/tag-form', [
            'title' => 'Edit Tag',
            'tag' => $tag,
            'errors' => $this->errors,
            'flashMessages' => $this->session->flash()->all(),
            'menuItems' => $this->buildMenuItems('tags'),
            'currentUser' => $this->getCurrentUser(),
            'activeSection' => 'tags',
        ]);
    }

    /**
     * Store a new tag (POST).
     */
    #[Post(path: '/mark/tags')]
    public function store(Request $request): Response
    {
        $name = trim((string) $request->post('name', ''));
        $slug = trim((string) $request->post('slug', ''));

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
            $this->errors['slug'] = 'A tag with this slug already exists';
        }

        if ($this->errors !== []) {
            error_log('[TagAdmin] Store - validation errors: ' . json_encode($this->errors));
            return $this->view->render('pages/mark/tag-form', [
                'title' => 'Create New Tag',
                'tag' => null,
                'errors' => $this->errors,
                'menuItems' => $this->buildMenuItems('tags'),
                'currentUser' => $this->getCurrentUser(),
                'activeSection' => 'tags',
            ]);
        }

        $this->getConnection()->execute(
            'INSERT INTO "tags" ("name", "slug") VALUES (?, ?)',
            [$name, $slug],
        );

        $this->session->flash()->add('success', 'Tag "' . $name . '" created successfully.');

        error_log('[TagAdmin] Tag created: ' . $name . ' (slug: ' . $slug . ')');

        return new Response('', 302, ['Location' => '/mark/tags']);
    }

    /**
     * Update an existing tag (PUT).
     */
    #[Put(path: '/mark/tags/{id}')]
    public function update(int $id, Request $request): Response
    {
        $tag = $this->findTag($id);

        if ($tag === null) {
            error_log('[TagAdmin] Update - tag not found: ' . $id);
            return new Response('Tag not found', 404);
        }

        $name = trim((string) $request->post('name', ''));
        $slug = trim((string) $request->post('slug', ''));

        // Validation
        $this->errors = [];
        if ($name === '') {
            $this->errors['name'] = 'Name is required';
        }
        if ($slug === '') {
            $slug = $this->generateSlug($name);
        }

        // Check slug uniqueness (exclude current tag)
        if ($this->errors === [] && $slug !== $tag['slug'] && $this->slugExists($slug)) {
            $this->errors['slug'] = 'A tag with this slug already exists';
        }

        if ($this->errors !== []) {
            error_log('[TagAdmin] Update - validation errors: ' . json_encode($this->errors));
            return $this->view->render('pages/mark/tag-form', [
                'title' => 'Edit Tag',
                'tag' => $tag,
                'errors' => $this->errors,
                'menuItems' => $this->buildMenuItems('tags'),
                'currentUser' => $this->getCurrentUser(),
                'activeSection' => 'tags',
            ]);
        }

        $this->getConnection()->execute(
            'UPDATE "tags" SET "name" = ?, "slug" = ? WHERE "id" = ?',
            [$name, $slug, $id],
        );

        $this->session->flash()->add('success', 'Tag "' . $name . '" updated successfully.');

        error_log('[TagAdmin] Tag updated: id=' . $id . ', name=' . $name);

        return new Response('', 302, ['Location' => '/mark/tags']);
    }

    /**
     * Delete a tag (DELETE).
     */
    #[Delete(path: '/mark/tags/{id}')]
    public function delete(int $id): Response
    {
        $tag = $this->findTag($id);

        if ($tag === null) {
            error_log('[TagAdmin] Delete - tag not found: ' . $id);
            return new Response('Tag not found', 404);
        }

        // Check if any articles reference this tag
        $articleCount = $this->getConnection()->query(
            'SELECT COUNT(*) as cnt FROM "article_tags" WHERE "tag_id" = ?',
            [$id],
        );

        if (($articleCount[0]['cnt'] ?? 0) > 0) {
            $this->session->flash()->add(
                'error',
                'Cannot delete tag "' . $tag['name'] . '": ' . $articleCount[0]['cnt'] . ' article(s) use it.',
            );
            error_log('[TagAdmin] Delete blocked - tag used by ' . $articleCount[0]['cnt'] . ' articles');

            return new Response('', 302, ['Location' => '/mark/tags']);
        }

        $this->getConnection()->execute(
            'DELETE FROM "tags" WHERE "id" = ?',
            [$id],
        );

        $this->session->flash()->add('success', 'Tag "' . $tag['name'] . '" deleted successfully.');

        error_log('[TagAdmin] Tag deleted: id=' . $id . ', name=' . $tag['name']);

        return new Response('', 302, ['Location' => '/mark/tags']);
    }

    /**
     * Fetch all tags from the database with article count.
     *
     * @return array<array{id: int, name: string, slug: string, article_count: int}>
     */
    private function fetchAllTags(): array
    {
        return $this->getConnection()->query(
            'SELECT t.*, (SELECT COUNT(*) FROM article_tags WHERE tag_id = t.id) as article_count FROM "tags" t ORDER BY "name"',
        );
    }

    /**
     * Find a single tag by ID.
     *
     * @return array{id: int, name: string, slug: string}|null
     */
    private function findTag(int $id): ?array
    {
        $results = $this->getConnection()->query(
            'SELECT * FROM "tags" WHERE "id" = ?',
            [$id],
        );
        return $results[0] ?? null;
    }

    /**
     * Check if a tag with the given slug already exists.
     */
    private function slugExists(string $slug): bool
    {
        $results = $this->getConnection()->query(
            'SELECT COUNT(*) as cnt FROM "tags" WHERE "slug" = ?',
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
