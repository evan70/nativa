<?php

declare(strict_types=1);

use App\Database\CardboardConnection;
use App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface;
use Marko\Cardboard\Controller\SettingsController;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\Session\Contracts\SessionInterface;
use Marko\View\ViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{
    private CardboardConnection&MockObject $cardboardConnection;
    private \Marko\Database\Connection\ConnectionInterface&MockObject $dbConnection;
    private ViewInterface&MockObject $view;
    private SessionInterface&MockObject $session;
    private \Marko\Admin\Contracts\AdminSectionRegistryInterface&MockObject $sectionRegistry;
    private \Marko\Authentication\Contracts\GuardInterface&MockObject $guard;
    private SettingsController $controller;

    protected function setUp(): void
    {
        $this->dbConnection = $this->createMock(\Marko\Database\Connection\ConnectionInterface::class);
        $this->cardboardConnection = $this->createMock(CardboardConnection::class);
        $this->view = $this->createMock(ViewInterface::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->sectionRegistry = $this->createMock(\Marko\Admin\Contracts\AdminSectionRegistryInterface::class);
        $this->guard = $this->createMock(\Marko\Authentication\Contracts\GuardInterface::class);

        $this->cardboardConnection
            ->method('getConnection')
            ->willReturn($this->dbConnection);

        $this->sectionRegistry
            ->method('all')
            ->willReturn([]);

        $this->guard
            ->method('user')
            ->willReturn(null);

        $this->controller = new SettingsController(
            $this->cardboardConnection,
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
            ->with($this->stringContains('SELECT * FROM "cardboard_settings"'))
            ->willReturn([]);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with('pages/mark/admin-settings', $this->isType('array'))
            ->willReturn(new Response(body: 'settings'));

        $response = $this->controller->index();

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testCreateReturnsView(): void
    {
        $this->view
            ->expects($this->once())
            ->method('render')
            ->with('pages/mark/settings-form', $this->isType('array'))
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
            'key' => 'test_key',
            'value' => 'test_value',
            'type' => 'string',
            'group' => 'general',
        ]);

        // Check existence (returns false)
        $this->dbConnection
            ->expects($this->once())
            ->method('query')
            ->with(
                $this->stringContains('COUNT'),
                $this->equalTo(['test_key']),
            )
            ->willReturn([['cnt' => 0]]);

        // Insert
        $this->dbConnection
            ->expects($this->once())
            ->method('execute')
            ->with(
                $this->stringContains('INSERT'),
                $this->callback(fn (array $params): bool => $params[0] === 'test_key'),
            )
            ->willReturn(1);

        $flash = $this->createMock(\Marko\Session\Flash\FlashBag::class);
        $flash
            ->expects($this->once())
            ->method('add')
            ->with('success', $this->stringContains('test_key'));

        $this->session
            ->expects($this->once())
            ->method('flash')
            ->willReturn($flash);

        $response = $this->controller->store($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->statusCode());
    }

    public function testStoreWithEmptyKeyReturnsError(): void
    {
        $request = new Request(post: [
            'key' => '',
            'value' => 'test_value',
            'type' => 'string',
            'group' => 'general',
        ]);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with(
                'pages/mark/settings-form',
                $this->callback(fn (array $data): bool =>
                    isset($data['errors']['key'])
                ),
            )
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->store($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testStoreWithInvalidKeyFormatReturnsError(): void
    {
        $request = new Request(post: [
            'key' => 'INVALID-KEY!',
            'value' => 'test_value',
            'type' => 'string',
            'group' => 'general',
        ]);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with(
                'pages/mark/settings-form',
                $this->callback(fn (array $data): bool =>
                    isset($data['errors']['key'])
                ),
            )
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->store($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testStoreWithInvalidTypeReturnsError(): void
    {
        $request = new Request(post: [
            'key' => 'valid_key',
            'value' => 'test_value',
            'type' => 'invalid_type',
            'group' => 'general',
        ]);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with(
                'pages/mark/settings-form',
                $this->callback(fn (array $data): bool =>
                    isset($data['errors']['type'])
                ),
            )
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->store($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testStoreWithDuplicateKeyReturnsError(): void
    {
        $request = new Request(post: [
            'key' => 'app_name',
            'value' => 'test',
            'type' => 'string',
            'group' => 'general',
        ]);

        // Existence check returns already existing
        $this->dbConnection
            ->expects($this->once())
            ->method('query')
            ->with(
                $this->stringContains('COUNT'),
                $this->equalTo(['app_name']),
            )
            ->willReturn([['cnt' => 1]]);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with(
                'pages/mark/settings-form',
                $this->callback(fn (array $data): bool =>
                    isset($data['errors']['key'])
                    && str_contains($data['errors']['key'], 'already exists')
                ),
            )
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->store($request);

        $this->assertInstanceOf(Response::class, $response);
    }
}
