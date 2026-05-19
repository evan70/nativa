<?php

declare(strict_types=1);

use App\Portfolio\Controller\PortfolioAdminController;
use App\Portfolio\Database\PortfolioConnection;
use App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\Session\Contracts\SessionInterface;
use Marko\View\ViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PortfolioAdminTest extends TestCase
{
    private PortfolioConnection&MockObject $portfolioConnection;
    private \Marko\Database\Connection\ConnectionInterface&MockObject $dbConnection;
    private ViewInterface&MockObject $view;
    private SessionInterface&MockObject $session;
    private \Marko\Admin\Contracts\AdminSectionRegistryInterface&MockObject $sectionRegistry;
    private \Marko\Authentication\Contracts\GuardInterface&MockObject $guard;
    private PortfolioAdminController $controller;

    protected function setUp(): void
    {
        $this->dbConnection = $this->createMock(\Marko\Database\Connection\ConnectionInterface::class);
        $this->portfolioConnection = $this->createMock(PortfolioConnection::class);
        $this->view = $this->createMock(ViewInterface::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->sectionRegistry = $this->createMock(\Marko\Admin\Contracts\AdminSectionRegistryInterface::class);
        $this->guard = $this->createMock(\Marko\Authentication\Contracts\GuardInterface::class);

        $this->portfolioConnection
            ->method('getConnection')
            ->willReturn($this->dbConnection);

        $this->sectionRegistry
            ->method('all')
            ->willReturn([]);

        $this->guard
            ->method('user')
            ->willReturn(null);

        $this->controller = new PortfolioAdminController(
            $this->portfolioConnection,
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
            ->with($this->stringContains('SELECT * FROM "portfolio_items"'))
            ->willReturn([]);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with('pages/mark/admin-portfolio-items', $this->isType('array'))
            ->willReturn(new Response(body: 'items'));

        $response = $this->controller->index();

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testCreateReturnsView(): void
    {
        $this->view
            ->expects($this->once())
            ->method('render')
            ->with('pages/mark/portfolio-item-form', $this->isType('array'))
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
            ->with('pages/errors/404', $this->isType('array'))
            ->willReturn('404');

        $response = $this->controller->edit(99999);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(404, $response->statusCode());
    }

    public function testStoreWithValidDataRedirects(): void
    {
        $request = new Request(post: [
            'title' => 'Test Project',
            'slug' => 'test-project',
            'subtitle' => 'A test project',
            'description' => 'This is a test description.',
            'category' => 'Web Development',
            'role' => 'Full Stack',
            'year' => '2026',
            'stack' => 'PHP, JavaScript',
            'image' => '',
            'display_order' => '1',
        ]);

        // Check slug uniqueness (returns false)
        $this->dbConnection
            ->expects($this->once())
            ->method('query')
            ->with(
                $this->stringContains('COUNT'),
                $this->equalTo(['test-project']),
            )
            ->willReturn([['cnt' => 0]]);

        // Insert
        $this->dbConnection
            ->expects($this->once())
            ->method('execute')
            ->with(
                $this->stringContains('INSERT'),
                $this->callback(fn (array $params): bool => $params[0] === 'Test Project'),
            )
            ->willReturn(1);

        $flash = $this->createMock(\Marko\Session\Flash\FlashBag::class);
        $flash
            ->expects($this->once())
            ->method('add')
            ->with('success', $this->stringContains('Test Project'));

        $this->session
            ->expects($this->once())
            ->method('flash')
            ->willReturn($flash);

        $response = $this->controller->store($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->statusCode());
    }

    public function testStoreWithEmptyTitleReturnsError(): void
    {
        $request = new Request(post: [
            'title' => '',
            'slug' => '',
            'subtitle' => '',
            'description' => 'Some description',
            'category' => '',
            'role' => '',
            'year' => '',
            'stack' => '',
            'image' => '',
            'display_order' => '0',
        ]);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with(
                'pages/mark/portfolio-item-form',
                $this->callback(fn (array $data): bool =>
                    isset($data['errors']['title'])
                ),
            )
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->store($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testStoreWithEmptyDescriptionReturnsError(): void
    {
        $request = new Request(post: [
            'title' => 'Valid Title',
            'slug' => '',
            'subtitle' => '',
            'description' => '',
            'category' => '',
            'role' => '',
            'year' => '',
            'stack' => '',
            'image' => '',
            'display_order' => '0',
        ]);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with(
                'pages/mark/portfolio-item-form',
                $this->callback(fn (array $data): bool =>
                    isset($data['errors']['description'])
                ),
            )
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->store($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testStoreWithDuplicateSlugReturnsError(): void
    {
        $request = new Request(post: [
            'title' => 'Test Project',
            'slug' => 'existing-slug',
            'subtitle' => '',
            'description' => 'A valid description.',
            'category' => '',
            'role' => '',
            'year' => '',
            'stack' => '',
            'image' => '',
            'display_order' => '0',
        ]);

        // Slug uniqueness check returns already exists
        $this->dbConnection
            ->expects($this->once())
            ->method('query')
            ->with(
                $this->stringContains('COUNT'),
                $this->equalTo(['existing-slug']),
            )
            ->willReturn([['cnt' => 1]]);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with(
                'pages/mark/portfolio-item-form',
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
        $existingItem = [
            'id' => 1,
            'title' => 'Old Title',
            'slug' => 'old-slug',
            'subtitle' => '',
            'description' => 'Old description.',
            'category' => '',
            'role' => '',
            'year' => '',
            'stack' => '',
            'image' => '',
            'display_order' => 0,
        ];

        // Find existing item (query #1) + slug uniqueness check (query #2)
        $queryCount = 0;
        $this->dbConnection
            ->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(function (string $sql, array $params = []) use ($existingItem, &$queryCount): array {
                $queryCount++;
                if ($queryCount === 1) {
                    // First query: find item by ID
                    $this->assertStringContainsString('WHERE "id" = ?', $sql);
                    $this->assertSame([1], $params);
                    return [$existingItem];
                }
                // Second query: slug uniqueness check
                $this->assertStringContainsString('COUNT', $sql);
                $this->assertSame(['new-slug'], $params);
                return [['cnt' => 0]];
            });

        // Update
        $this->dbConnection
            ->expects($this->once())
            ->method('execute')
            ->with(
                $this->stringContains('UPDATE'),
                $this->callback(fn (array $params): bool => $params[0] === 'New Title'),
            )
            ->willReturn(1);

        $request = new Request(post: [
            'title' => 'New Title',
            'slug' => 'new-slug',
            'subtitle' => 'Updated subtitle',
            'description' => 'Updated description.',
            'category' => 'Design',
            'role' => 'Frontend',
            'year' => '2025',
            'stack' => 'CSS, HTML',
            'image' => '/img/new.jpg',
            'display_order' => '2',
        ]);

        $flash = $this->createMock(\Marko\Session\Flash\FlashBag::class);
        $flash
            ->expects($this->once())
            ->method('add')
            ->with('success', $this->stringContains('New Title'));

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
        $existingItem = [
            'id' => 1,
            'title' => 'Delete Me',
            'slug' => 'delete-me',
            'subtitle' => '',
            'description' => 'To be deleted.',
            'category' => '',
            'role' => '',
            'year' => '',
            'stack' => '',
            'image' => '',
            'display_order' => 0,
        ];

        // Find existing item
        $this->dbConnection
            ->expects($this->once())
            ->method('query')
            ->with(
                $this->stringContains('WHERE "id" = ?'),
                $this->equalTo([1]),
            )
            ->willReturn([$existingItem]);

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
