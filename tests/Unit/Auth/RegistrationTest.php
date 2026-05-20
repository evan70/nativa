<?php

declare(strict_types=1);

use Marko\Authentication\Contracts\GuardInterface;
use Marko\Authentication\Contracts\PasswordHasherInterface;
use Marko\Cardboard\Controller\RegisterController;
use Marko\Mark\Entity\Mark;
use Marko\Mark\Repository\MarkRepositoryInterface;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RegistrationTest extends TestCase
{
    private ViewInterface&MockObject $view;
    private MarkRepositoryInterface&MockObject $repository;
    private GuardInterface&MockObject $guard;
    private PasswordHasherInterface&MockObject $hasher;
    private RegisterController $controller;

    protected function setUp(): void
    {
        $this->view = $this->createMock(ViewInterface::class);
        $this->repository = $this->createMock(MarkRepositoryInterface::class);
        $this->guard = $this->createMock(GuardInterface::class);
        $this->hasher = $this->createMock(PasswordHasherInterface::class);
        $this->controller = new RegisterController(
            $this->view,
            $this->repository,
            $this->guard,
            $this->hasher,
        );
    }

    private function makeRequest(array $post = []): Request
    {
        return new Request(post: $post);
    }

    public function testShowRegistrationFormReturnsView(): void
    {
        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(false);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with('pages/auth/register', $this->isArray())
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->showRegistrationForm($this->makeRequest());

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testShowRegistrationFormRedirectsWhenAuthenticated(): void
    {
        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(true);

        $response = $this->controller->showRegistrationForm($this->makeRequest());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->statusCode());
    }

    public function testRegisterWithValidDataCreatesUserAndRedirects(): void
    {
        $request = $this->makeRequest([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(false);

        $this->repository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('john@example.com')
            ->willReturn(null);

        $this->hasher
            ->expects($this->once())
            ->method('hash')
            ->with('password123')
            ->willReturn('$2y$12$hashedpassword');

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Mark::class));

        $this->guard
            ->expects($this->once())
            ->method('login')
            ->with($this->isInstanceOf(Mark::class));

        $response = $this->controller->register($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(302, $response->statusCode());
    }

    public function testRegisterWithDuplicateEmailReturnsError(): void
    {
        $request = $this->makeRequest([
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(false);

        $existingUser = new Mark();
        $existingUser->email = 'existing@example.com';

        $this->repository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('existing@example.com')
            ->willReturn($existingUser);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with(
                'pages/auth/register',
                $this->callback(fn (array $data): bool =>
                    isset($data['errors']['email'])
                    && str_contains($data['errors']['email'], 'already exists')
                ),
            )
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->register($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testRegisterWithPasswordMismatchReturnsError(): void
    {
        $request = $this->makeRequest([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(false);

        $this->repository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('john@example.com')
            ->willReturn(null);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with(
                'pages/auth/register',
                $this->callback(fn (array $data): bool =>
                    isset($data['errors']['password_confirmation'])
                ),
            )
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->register($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testRegisterWithEmptyFieldsReturnsErrors(): void
    {
        $request = $this->makeRequest([
            'name' => '',
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
            ->with(
                'pages/auth/register',
                $this->callback(fn (array $data): bool =>
                    isset($data['errors']['name'])
                    && isset($data['errors']['email'])
                    && isset($data['errors']['password'])
                    && isset($data['errors']['password_confirmation'])
                ),
            )
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->register($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testRegisterWithShortPasswordReturnsError(): void
    {
        $request = $this->makeRequest([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $this->guard
            ->expects($this->once())
            ->method('check')
            ->willReturn(false);

        $this->repository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('john@example.com')
            ->willReturn(null);

        $this->view
            ->expects($this->once())
            ->method('render')
            ->with(
                'pages/auth/register',
                $this->callback(fn (array $data): bool =>
                    isset($data['errors']['password'])
                    && str_contains($data['errors']['password'], '8 characters')
                ),
            )
            ->willReturn(new Response(body: 'form'));

        $response = $this->controller->register($request);

        $this->assertInstanceOf(Response::class, $response);
    }
}
