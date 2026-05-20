<?php

declare(strict_types=1);

use App\Blog\Controller\TagAdminController;
use App\Blog\Database\BlogConnection;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\Session\Contracts\SessionInterface;
use Marko\View\ViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TagAdminTest extends TestCase
{
    private BlogConnection&MockObject $blogConnection;
    private \Marko\Database\Connection\ConnectionInterface&MockObject $dbConnection;
    private ViewInterface&MockObject $view;
    private SessionInterface&MockObject $session;
    private \Marko\Admin\Contracts\AdminSectionRegistryInterface&MockObject $sectionRegistry;
    private \Marko\Authentication\Contracts\GuardInterface&MockObject $guard;
    private TagAdminController $controller;

    protected function setUp(): void
    {
        $this->dbConnection = $this->createMock(\Marko\Database\Connection\ConnectionInterface::class);
        $this->blogConnection = $this->createMock(BlogConnection::class);
        $this->view = $this->createMock(ViewInterface::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->sectionRegistry = $this->createMock(\Marko\Admin\Contracts\AdminSectionRegistryInterface::class);
        $this->guard = $this->createMock(\Marko\Authentication\Contracts\GuardInterface::class);

        $this->blogConnection
            ->method('getConnection')
            ->willReturn($this->dbConnection);

        $this->sectionRegistry
            ->method('all')
            ->willReturn([]);

        $this->guard
            ->method('user')
            ->willReturn(null);

        $this->controller = new TagAdminController(
            $this->blogConnection,
            $this->view,
            $this->session,
            $this->sectionRegistry,
            $this->guard,
        );
    }

    public function testIndexReturnsView(): void
    {
        $this->dbConnection
            ->expects($this->once())
            ->method('query')
            ->with($this->stringContains('SELECT t.*'))
            ->willReturn([]);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with('pages/mark/tags', $this->isArray())
            ->willReturn(new Response(body: 'tags'));

        $response = $this->controller->index();

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testCreateReturnsView(): void
    {
        $this->view
            ->expects($this->once())
            ->method('render')
            ->with('pages/mark/tag-form', $this->isArray())
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->create();

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testEditWithNonExistentIdReturns404(): void
    {
        $this->dbConnection
            ->expects($this->once())
            ->method('query')
            ->with(
                $this->stringContains('WHERE "id" = ?'),
                $this->equalTo([99999]),
            )
            ->willReturn([]);

        $this->view
            ->expects($this->once())
            ->method('renderToString')
            ->with('pages/errors/404', $this->isArray())
            ->willReturn('404');

        $response = $this->controller->edit(99999);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(404, $response->statusCode());
    }

    public function testStoreWithValidDataRedirects(): void
    {
        $request = new Request(post: [
            'name' => 'JavaScript',
            'slug' => 'javascript',
        ]);

        // Check slug uniqueness (returns false)
        $this->dbConnection
            ->expects($this->once())
            ->method('query')
            ->with(
                $this->stringContains('COUNT'),
                $this->equalTo(['javascript']),
            )
            ->willReturn([['cnt' => 0]]);

        // Insert
        $this->dbConnection
            ->expects($this->once())
            ->method('execute')
            ->with(
                $this->stringContains('INSERT'),
                $this->callback(fn (array $params): bool => $params[0] === 'JavaScript'),
            )
            ->willReturn(1);

        $flash = $this->createMock(\Marko\Session\Flash\FlashBag::class);
        $flash
            ->expects($this->once())
            ->method('add')
            ->with('success', $this->stringContains('JavaScript'));

        $this->session
            ->expects($this->once())
            ->method('flash')
            ->willReturn($flash);

        $response = $this->controller->store($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->statusCode());
    }

    public function testStoreWithEmptyNameReturnsError(): void
    {
        $request = new Request(post: [
            'name' => '',
            'slug' => '',
        ]);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with(
                'pages/mark/tag-form',
                $this->callback(fn (array $data): bool =>
                    isset($data['errors']['name'])
                ),
            )
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->store($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testStoreWithDuplicateSlugReturnsError(): void
    {
        $request = new Request(post: [
            'name' => 'PHP',
            'slug' => 'php',
        ]);

        // Slug uniqueness check returns already exists
        $this->dbConnection
            ->expects($this->once())
            ->method('query')
            ->with(
                $this->stringContains('COUNT'),
                $this->equalTo(['php']),
            )
            ->willReturn([['cnt' => 1]]);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with(
                'pages/mark/tag-form',
                $this->callback(fn (array $data): bool =>
                    isset($data['errors']['slug'])
                    && str_contains($data['errors']['slug'], 'already exists')
                ),
            )
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->store($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testUpdateWithValidDataRedirects(): void
    {
        $existingTag = [
            'id' => 1,
            'name' => 'Old Name',
            'slug' => 'old-name',
        ];

        // Find existing item + slug uniqueness check
        $queryCount = 0;
        $this->dbConnection
            ->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(function (string $sql, array $params = []) use ($existingTag, &$queryCount): array {
                $queryCount++;
                if ($queryCount === 1) {
                    $this->assertStringContainsString('WHERE "id" = ?', $sql);
                    $this->assertSame([1], $params);
                    return [$existingTag];
                }
                $this->assertStringContainsString('COUNT', $sql);
                $this->assertSame(['new-name'], $params);
                return [['cnt' => 0]];
            });

        // Update
        $this->dbConnection
            ->expects($this->once())
            ->method('execute')
            ->with(
                $this->stringContains('UPDATE'),
                $this->callback(fn (array $params): bool => $params[0] === 'New Name'),
            )
            ->willReturn(1);

        $request = new Request(post: [
            'name' => 'New Name',
            'slug' => 'new-name',
        ]);

        $flash = $this->createMock(\Marko\Session\Flash\FlashBag::class);
        $flash
            ->expects($this->once())
            ->method('add')
            ->with('success', $this->stringContains('New Name'));

        $this->session
            ->expects($this->once())
            ->method('flash')
            ->willReturn($flash);

        $response = $this->controller->update(1, $request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->statusCode());
    }

    public function testUpdateWithNonExistentIdReturns404(): void
    {
        $this->dbConnection
            ->expects($this->once())
            ->method('query')
            ->with(
                $this->stringContains('WHERE "id" = ?'),
                $this->equalTo([99999]),
            )
            ->willReturn([]);

        $request = new Request(post: []);
        $response = $this->controller->update(99999, $request);

        $this->assertEquals(404, $response->statusCode());
    }

    public function testDeleteRedirects(): void
    {
        $existingTag = [
            'id' => 1,
            'name' => 'Delete Me',
            'slug' => 'delete-me',
        ];

        $this->dbConnection
            ->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(function (string $sql, array $params = []) use ($existingTag): array {
                if (str_contains($sql, 'WHERE "id" = ?')) {
                    $this->assertSame([1], $params);
                    return [$existingTag];
                }
                // Article count check - zero articles reference it
                $this->assertSame([1], $params);
                return [['cnt' => 0]];
            });

        // Delete
        $this->dbConnection
            ->expects($this->once())
            ->method('execute')
            ->with(
                $this->stringContains('DELETE'),
                $this->equalTo([1]),
            )
            ->willReturn(1);

        $flash = $this->createMock(\Marko\Session\Flash\FlashBag::class);
        $flash
            ->expects($this->once())
            ->method('add')
            ->with('success', $this->stringContains('Delete Me'));

        $this->session
            ->expects($this->once())
            ->method('flash')
            ->willReturn($flash);

        $response = $this->controller->delete(1);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->statusCode());
    }

    public function testDeleteWithReferencedArticlesIsBlocked(): void
    {
        $existingTag = [
            'id' => 1,
            'name' => 'Referenced',
            'slug' => 'referenced',
        ];

        // Find existing item + article count check (2 articles)
        $queryCount = 0;
        $this->dbConnection
            ->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(function (string $sql, array $params = []) use ($existingTag, &$queryCount): array {
                $queryCount++;
                if ($queryCount === 1) {
                    return [$existingTag];
                }
                // 2 articles reference this tag
                return [['cnt' => 2]];
            });

        // NO execute call should happen (delete blocked)
        $this->dbConnection
            ->expects($this->never())
            ->method('execute');

        $flash = $this->createMock(\Marko\Session\Flash\FlashBag::class);
        $flash
            ->expects($this->once())
            ->method('add')
            ->with('error', $this->stringContains('Cannot delete'));

        $this->session
            ->expects($this->once())
            ->method('flash')
            ->willReturn($flash);

        $response = $this->controller->delete(1);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->statusCode());
    }

    public function testDeleteWithNonExistentIdReturns404(): void
    {
        $this->dbConnection
            ->expects($this->once())
            ->method('query')
            ->with(
                $this->stringContains('WHERE "id" = ?'),
                $this->equalTo([99999]),
            )
            ->willReturn([]);

        $response = $this->controller->delete(99999);

        $this->assertEquals(404, $response->statusCode());
    }
}
