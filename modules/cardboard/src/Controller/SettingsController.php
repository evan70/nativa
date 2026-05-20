<?php

declare(strict_types=1);

namespace Marko\Cardboard\Controller;

use App\Database\CardboardConnection;
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
class SettingsController
{
    /** @var array<string, string> */
    private array $errors = [];

    public function __construct(
        private readonly CardboardConnection $cardboardConnection,
        private readonly ViewInterface $view,
        private readonly SessionInterface $session,
        private readonly AdminSectionRegistryInterface $sectionRegistry,
        private readonly GuardInterface $guard,
    ) {}

    /**
     * List all settings.
     */
    #[Get(path: '/admin/cardboard/settings')]
    public function index(): Response
    {
        $settings = $this->fetchAllSettings();

        error_log('[Settings] Index - showing ' . count($settings) . ' settings');

        return $this->view->render('pages/mark/admin-settings', [
            'title' => 'Cardboard Settings',
            'settings' => $settings,
            'flashMessages' => $this->session->flash()->all(),
            'menuItems' => $this->buildMenuItems('settings'),
            'currentUser' => $this->getCurrentUser(),
            'activeSection' => 'settings',
        ]);
    }

    /**
     * Form to create a new setting.
     */
    #[Get(path: '/admin/cardboard/settings/new')]
    public function create(): Response
    {
        error_log('[Settings] Create form accessed');

        return $this->view->render('pages/mark/settings-form', [
            'title' => 'Create New Setting',
            'setting' => null,
            'errors' => $this->errors,
            'flashMessages' => $this->session->flash()->all(),
            'menuItems' => $this->buildMenuItems('settings'),
            'currentUser' => $this->getCurrentUser(),
            'activeSection' => 'settings',
        ]);
    }

    /**
     * Form to edit an existing setting.
     */
    #[Get(path: '/admin/cardboard/settings/{id}/edit')]
    public function edit(int $id): Response
    {
        $setting = $this->findSetting($id);

        if ($setting === null) {
            error_log('[Settings] Edit - setting not found: ' . $id);
            return Response::html($this->view->renderToString('pages/errors/404', [
                'title' => 'Setting Not Found',
            ]), 404);
        }

        error_log('[Settings] Edit form for setting: ' . $id);

        return $this->view->render('pages/mark/settings-form', [
            'title' => 'Edit Setting',
            'setting' => $setting,
            'errors' => $this->errors,
            'flashMessages' => $this->session->flash()->all(),
            'menuItems' => $this->buildMenuItems('settings'),
            'currentUser' => $this->getCurrentUser(),
            'activeSection' => 'settings',
        ]);
    }

    /**
     * Store a new setting (POST).
     */
    #[Post(path: '/admin/cardboard/settings')]
    public function store(Request $request): Response
    {
        $keyRaw = $request->post('key', '');
        $key = is_string($keyRaw) ? trim($keyRaw) : '';
        $valueRaw = $request->post('value', '');
        $value = is_string($valueRaw) ? trim($valueRaw) : '';
        $typeRaw = $request->post('type', 'string');
        $type = is_string($typeRaw) ? trim($typeRaw) : 'string';
        $groupRaw = $request->post('group', 'general');
        $group = is_string($groupRaw) ? trim($groupRaw) : 'general';

        // Validation
        $this->errors = [];
        if ($key === '') {
            $this->errors['key'] = 'Key is required';
        }
        if (!preg_match('/^[a-z_][a-z0-9_]*$/', $key)) {
            $this->errors['key'] = 'Key must start with a letter/underscore and contain only lowercase letters, numbers, and underscores';
        }
        if (!in_array($type, ['string', 'number', 'boolean', 'json'], true)) {
            $this->errors['type'] = 'Invalid type. Must be: string, number, boolean, or json';
        }

        // Check uniqueness
        if ($this->errors === [] && $this->settingExists($key)) {
            $this->errors['key'] = 'A setting with this key already exists';
        }

        if ($this->errors !== []) {
            error_log('[Settings] Store - validation errors: ' . json_encode($this->errors));
            return $this->view->render('pages/mark/settings-form', [
                'title' => 'Create New Setting',
                'setting' => null,
                'errors' => $this->errors,
                'menuItems' => $this->buildMenuItems('settings'),
                'currentUser' => $this->getCurrentUser(),
                'activeSection' => 'settings',
            ]);
        }

        $this->getConnection()->execute(
            'INSERT INTO "cardboard_settings" ("key", "value", "type", "group", "createdAt", "updatedAt") VALUES (?, ?, ?, ?, ?, ?)',
            [$key, $value, $type, $group, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')],
        );

        $this->session->flash()->add('success', 'Setting "' . $key . '" created successfully.');

        error_log('[Settings] Setting created: ' . $key);

        return new Response('', 302, ['Location' => '/admin/cardboard/settings']);
    }

    /**
     * Update an existing setting (PUT).
     */
    #[Put(path: '/admin/cardboard/settings/{id}')]
    public function update(int $id, Request $request): Response
    {
        $setting = $this->findSetting($id);

        if ($setting === null) {
            error_log('[Settings] Update - setting not found: ' . $id);
            return new Response('Setting not found', 404);
        }

        $valueRaw = $request->post('value', '');
        $value = is_string($valueRaw) ? trim($valueRaw) : '';
        $typeRaw = $request->post('type', 'string');
        $type = is_string($typeRaw) ? trim($typeRaw) : 'string';
        $groupRaw = $request->post('group', 'general');
        $group = is_string($groupRaw) ? trim($groupRaw) : 'general';

        // Validation
        $this->errors = [];
        if (!in_array($type, ['string', 'number', 'boolean', 'json'], true)) {
            $this->errors['type'] = 'Invalid type. Must be: string, number, boolean, or json';
        }

        if ($this->errors !== []) {
            error_log('[Settings] Update - validation errors: ' . json_encode($this->errors));
            return $this->view->render('pages/mark/settings-form', [
                'title' => 'Edit Setting',
                'setting' => $setting,
                'errors' => $this->errors,
                'menuItems' => $this->buildMenuItems('settings'),
                'currentUser' => $this->getCurrentUser(),
                'activeSection' => 'settings',
            ]);
        }

        $this->getConnection()->execute(
            'UPDATE "cardboard_settings" SET "value" = ?, "type" = ?, "group" = ?, "updatedAt" = ? WHERE "id" = ?',
            [$value, $type, $group, date('Y-m-d H:i:s'), $id],
        );

        $this->session->flash()->add('success', 'Setting "' . $setting['key'] . '" updated successfully.');

        error_log('[Settings] Setting updated: id=' . $id . ', key=' . $setting['key']);

        return new Response('', 302, ['Location' => '/admin/cardboard/settings']);
    }

    /**
     * Delete a setting (DELETE).
     */
    #[Delete(path: '/admin/cardboard/settings/{id}')]
    public function delete(int $id): Response
    {
        $setting = $this->findSetting($id);

        if ($setting === null) {
            error_log('[Settings] Delete - setting not found: ' . $id);
            return new Response('Setting not found', 404);
        }

        $this->getConnection()->execute(
            'DELETE FROM "cardboard_settings" WHERE "id" = ?',
            [$id],
        );

        $this->session->flash()->add('success', 'Setting "' . $setting['key'] . '" deleted successfully.');

        error_log('[Settings] Setting deleted: id=' . $id . ', key=' . $setting['key']);

        return new Response('', 302, ['Location' => '/admin/cardboard/settings']);
    }

    /**
     * Fetch all settings from the database.
     *
     * @return array<array{id: int, key: string, value: string, type: string, group: string, createdAt: string, updatedAt: string}>
     */
    private function fetchAllSettings(): array
    {
        /** @var array<array{id: int, key: string, value: string, type: string, group: string, createdAt: string, updatedAt: string}> $results */
        $results = $this->getConnection()->query(
            'SELECT * FROM "cardboard_settings" ORDER BY "group", "key"',
        );
        return $results;
    }

    /**
     * Find a single setting by ID.
     *
     * @return array{id: int, key: string, value: string, type: string, group: string, createdAt: string, updatedAt: string}|null
     */
    private function findSetting(int $id): ?array
    {
        /** @var array<array{id: int, key: string, value: string, type: string, group: string, createdAt: string, updatedAt: string}> $results */
        $results = $this->getConnection()->query(
            'SELECT * FROM "cardboard_settings" WHERE "id" = ?',
            [$id],
        );
        return $results[0] ?? null;
    }

    /**
     * Check if a setting with the given key already exists.
     */
    private function settingExists(string $key): bool
    {
        $results = $this->getConnection()->query(
            'SELECT COUNT(*) as cnt FROM "cardboard_settings" WHERE "key" = ?',
            [$key],
        );
        return ($results[0]['cnt'] ?? 0) > 0;
    }

    private function getConnection(): \Marko\Database\Connection\ConnectionInterface
    {
        return $this->cardboardConnection->getConnection();
    }

    /**
     * @return array<int, array{url: string, label: string, icon: string, active: bool}>
     */
    private function buildMenuItems(string $currentSlug = ''): array
    {
        $items = [];
        foreach ($this->sectionRegistry->all() as $section) {
            $slug = $section->getId();
            $items[] = [
                'url' => '/mark' . ($slug !== 'dashboard' ? '/' . $slug : ''),
                'label' => $section->getLabel(),
                'icon' => $section->getIcon(),
                'active' => $slug === $currentSlug,
            ];
        }
        return $items;
    }

    private function getCurrentUser(): ?\Marko\Authentication\AuthenticatableInterface
    {
        return $this->guard->user();
    }
}
