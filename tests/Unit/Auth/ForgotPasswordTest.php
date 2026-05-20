<?php

declare(strict_types=1);

use Marko\Authentication\Contracts\GuardInterface;
use Marko\Cardboard\Controller\ForgotPasswordController;
use Marko\Cardboard\Service\PasswordResetService;
use Marko\Mark\Repository\MarkRepositoryInterface;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ForgotPasswordTest extends TestCase
{
    private ViewInterface&MockObject $view;
    private GuardInterface&MockObject $guard;
    private MarkRepositoryInterface&MockObject $repository;
    private PasswordResetService&MockObject $resetService;
    private ForgotPasswordController $controller;

    protected function setUp(): void
    {
        $this->view = $this->createMock(ViewInterface::class);
        $this->guard = $this->createMock(GuardInterface::class);
        $this->repository = $this->createMock(MarkRepositoryInterface::class);
        $this->resetService = $this->createMock(PasswordResetService::class);
        $this->controller = new ForgotPasswordController(
            $this->view,
            $this->guard,
            $this->repository,
            $this->resetService,
        );
    }

    private function makeRequest(array $post = []): Request
    {
        return new Request(post: $post);
    }

    public function testShowForgotFormReturnsView(): void
    {
        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(false);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with('pages/auth/forgot-password', $this->isArray())
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->showForgotForm($this->makeRequest());

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testShowForgotFormRedirectsWhenAuthenticated(): void
    {
        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(true);

        $response = $this->controller->showForgotForm($this->makeRequest());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->statusCode());
    }

    public function testSendResetWithValidEmailReturnsSuccess(): void
    {
        $request = $this->makeRequest([
            'email' => 'admin@marko.local',
        ]);

        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(false);

        $this->repository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('admin@marko.local')
            ->willReturn(new \Marko\Mark\Entity\Mark());

        $this->resetService
            ->expects($this->once())
            ->method('generateToken')
            ->with('admin@marko.local')
            ->willReturn('test-token-123');

        $this->resetService
            ->expects($this->once())
            ->method('sendResetEmail')
            ->with('admin@marko.local', 'test-token-123', '/mark/reset-password/test-token-123');

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with('pages/auth/forgot-password', $this->callback(fn (array $data): bool =>
                isset($data['success']) && $data['success'] === true
            ))
            ->willReturn(new Response(body: 'success'));

        $response = $this->controller->sendReset($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testSendResetWithInvalidEmailStillReturnsSuccess(): void
    {
        $request = $this->makeRequest([
            'email' => 'nonexistent@example.com',
        ]);

        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(false);

        $this->repository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('nonexistent@example.com')
            ->willReturn(null);

        $this->resetService
            ->expects($this->never())
            ->method('generateToken');

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with('pages/auth/forgot-password', $this->callback(fn (array $data): bool =>
                isset($data['success']) && $data['success'] === true
            ))
            ->willReturn(new Response(body: 'success'));

        $response = $this->controller->sendReset($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testSendResetWithEmptyEmailReturnsError(): void
    {
        $request = $this->makeRequest([
            'email' => '',
        ]);

        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(false);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with(
                'pages/auth/forgot-password',
                $this->callback(fn (array $data): bool =>
                    isset($data['errors']['email'])
                ),
            )
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->sendReset($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testSendResetRedirectsWhenAuthenticated(): void
    {
        $request = $this->makeRequest([
            'email' => 'admin@marko.local',
        ]);

        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(true);

        $response = $this->controller->sendReset($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->statusCode());
    }
}
