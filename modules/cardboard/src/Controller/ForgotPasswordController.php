<?php

declare(strict_types=1);

namespace Marko\Cardboard\Controller;

use Marko\Authentication\Contracts\GuardInterface;
use Marko\Cardboard\Service\PasswordResetService;
use Marko\Mark\Repository\MarkRepositoryInterface;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Post;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;

class ForgotPasswordController
{
    public function __construct(
        private readonly ViewInterface $view,
        private readonly GuardInterface $guard,
        private readonly MarkRepositoryInterface $repository,
        private readonly PasswordResetService $resetService,
    ) {}

    #[Get(path: '/mark/forgot-password')]
    public function showForgotForm(
        Request $request,
    ): Response {
        // Already logged in — redirect to dashboard
        if ($this->guard->check()) {
            return Response::redirect('/mark');
        }

        return $this->view->render('pages/auth/forgot-password', [
            'old' => [],
            'success' => false,
            'errors' => [],
        ]);
    }

    #[Post(path: '/mark/forgot-password')]
    public function sendReset(
        Request $request,
    ): Response {
        // Already logged in — redirect
        if ($this->guard->check()) {
            return Response::redirect('/mark');
        }

        $email = trim((string) $request->post('email', ''));
        $errors = [];

        if ($email === '') {
            $errors['email'] = 'Please enter your email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if ($errors !== []) {
            return $this->view->render('pages/auth/forgot-password', [
                'old' => ['email' => $email],
                'success' => false,
                'errors' => $errors,
            ]);
        }

        // Always show success to avoid leaking whether an email exists
        $user = $this->repository->findByEmail($email);

        if ($user !== null) {
            $token = $this->resetService->generateToken($email);
            $resetUrl = '/mark/reset-password/' . $token;

            $this->resetService->sendResetEmail($email, $token, $resetUrl);
        } else {
            error_log('[ForgotPassword] Email not found: ' . $email);
        }

        // Always return success
        return $this->view->render('pages/auth/forgot-password', [
            'old' => [],
            'success' => true,
            'errors' => [],
        ]);
    }
}
