<?php

declare(strict_types=1);

use Marko\Authentication\Contracts\GuardInterface;
use Marko\Authentication\Contracts\PasswordHasherInterface;
use Marko\Cardboard\Controller\ResetPasswordController;
use Marko\Cardboard\Service\PasswordResetService;
use Marko\Mark\Entity\Mark;
use Marko\Mark\Repository\MarkRepositoryInterface;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResetPasswordTest extends TestCase
{
    private ViewInterface&MockObject $view;
    private GuardInterface&MockObject $guard;
    private MarkRepositoryInterface&MockObject $repository;
    private PasswordHasherInterface&MockObject $hasher;
    private PasswordResetService&MockObject $resetService;
    private ResetPasswordController $controller;

    protected function setUp(): void
    {
        $this->view = $this->createMock(ViewInterface::class);
        $this->guard = $this->createMock(GuardInterface::class);
        $this->repository = $this->createMock(MarkRepositoryInterface::class);
        $this->hasher = $this->createMock(PasswordHasherInterface::class);
        $this->resetService = $this->createMock(PasswordResetService::class);
        $this->controller = new ResetPasswordController(
            $this->view,
            $this->guard,
            $this->repository,
            $this->hasher,
            $this->resetService,
        );
    }

    private function makeRequest(array $post = []): Request
    {
        return new Request(post: $post);
    }

    public function testShowResetFormReturnsView(): void
    {
        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(false);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with('pages/auth/reset-password', $this->callback(fn (array $data): bool =>
                isset($data['token']) && $data['token'] === 'test-token-abc'
            ))
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->showResetForm($this->makeRequest(), 'test-token-abc');

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testShowResetFormRedirectsWhenAuthenticated(): void
    {
        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(true);

        $response = $this->controller->showResetForm($this->makeRequest(), 'test-token-abc');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->statusCode());
    }

    public function testResetWithValidTokenAndMatchingPasswordsUpdatesPassword(): void
    {
        $request = $this->makeRequest([
            'email' => 'admin@marko.local',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(false);

        $this->resetService
            ->expects($this->once())
            ->method('validateToken')
            ->with('admin@marko.local', 'valid-token')
            ->willReturn(true);

        $user = new Mark();
        $user->id = 1;
        $user->email = 'admin@marko.local';

        $this->repository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('admin@marko.local')
            ->willReturn($user);

        $this->hasher
            ->expects($this->once())
            ->method('hash')
            ->with('newpassword123')
            ->willReturn('$2y$12$hashednewpassword');

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Mark::class));

        $this->resetService
            ->expects($this->once())
            ->method('deleteToken')
            ->with('admin@marko.local');

        $response = $this->controller->reset($request, 'valid-token');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->statusCode());
    }

    public function testResetWithInvalidTokenReturnsError(): void
    {
        $request = $this->makeRequest([
            'email' => 'admin@marko.local',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(false);

        $this->resetService
            ->expects($this->once())
            ->method('validateToken')
            ->with('admin@marko.local', 'invalid-token')
            ->willReturn(false);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with('pages/auth/reset-password', $this->callback(fn (array $data): bool =>
                isset($data['errors']['token'])
            ))
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->reset($request, 'invalid-token');

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testResetWithPasswordMismatchReturnsError(): void
    {
        $request = $this->makeRequest([
            'email' => 'admin@marko.local',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(false);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with('pages/auth/reset-password', $this->callback(fn (array $data): bool =>
                isset($data['errors']['password_confirmation'])
            ))
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->reset($request, 'some-token');

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testResetWithEmptyFieldsReturnsErrors(): void
    {
        $request = $this->makeRequest([
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(false);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with('pages/auth/reset-password', $this->callback(fn (array $data): bool =>
                isset($data['errors']['email'])
                && isset($data['errors']['password'])
                && isset($data['errors']['password_confirmation'])
            ))
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->reset($request, 'some-token');

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testResetRedirectsWhenAuthenticated(): void
    {
        $request = $this->makeRequest([
            'email' => 'admin@marko.local',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(true);

        $response = $this->controller->reset($request, 'some-token');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->statusCode());
    }
}
