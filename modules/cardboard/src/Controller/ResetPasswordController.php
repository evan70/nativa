<?php

declare(strict_types=1);

namespace Marko\Cardboard\Controller;

use Marko\Authentication\Contracts\GuardInterface;
use Marko\Authentication\Contracts\PasswordHasherInterface;
use Marko\Cardboard\Service\PasswordResetService;
use Marko\Mark\Entity\Mark;
use Marko\Mark\Repository\MarkRepositoryInterface;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Post;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;

class ResetPasswordController
{
    /**
     * @param MarkRepositoryInterface<Mark> $repository
     */
    public function __construct(
        private readonly ViewInterface $view,
        private readonly GuardInterface $guard,
        private readonly MarkRepositoryInterface $repository,
        private readonly PasswordHasherInterface $hasher,
        private readonly PasswordResetService $resetService,
    ) {}

    #[Get(path: '/mark/reset-password/{token}')]
    public function showResetForm(
        Request $request,
        string $token,
    ): Response {
        // Already logged in — redirect to dashboard
        if ($this->guard->check()) {
            return Response::redirect('/mark');
        }

        // We need the email from the token — but we don't have it yet.
        // The user will enter their email on the reset form along with the new password.
        // Actually from the URL, the token is in the path. We need to pass it to the form
        // so the POST request can use it.
        return $this->view->render('pages/auth/reset-password', [
            'token' => $token,
            'errors' => [],
            'success' => false,
        ]);
    }

    #[Post(path: '/mark/reset-password/{token}')]
    public function reset(
        Request $request,
        string $token,
    ): Response {
        // Already logged in — redirect
        if ($this->guard->check()) {
            return Response::redirect('/mark');
        }

        $emailRaw = $request->post('email', '');
        $email = is_string($emailRaw) ? trim($emailRaw) : '';
        $passwordRaw = $request->post('password', '');
        $password = is_string($passwordRaw) ? $passwordRaw : '';
        $passwordConfirmationRaw = $request->post('password_confirmation', '');
        $passwordConfirmation = is_string($passwordConfirmationRaw) ? $passwordConfirmationRaw : '';
        $errors = [];

        // Validate email
        if ($email === '') {
            $errors['email'] = 'Please enter your email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        // Validate password
        if ($password === '') {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        // Validate password confirmation
        if ($passwordConfirmation === '') {
            $errors['password_confirmation'] = 'Please confirm your password.';
        } elseif ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'Passwords do not match.';
        }

        if ($errors !== []) {
            error_log('[ResetPassword] Validation errors: ' . json_encode($errors));
            return $this->view->render('pages/auth/reset-password', [
                'token' => $token,
                'errors' => $errors,
                'success' => false,
            ]);
        }

        // Validate token
        if (!$this->resetService->validateToken($email, $token)) {
            $errors['token'] = 'This reset link is invalid or has expired. Please request a new one.';
            error_log('[ResetPassword] Token validation failed for email=' . $email);
            return $this->view->render('pages/auth/reset-password', [
                'token' => $token,
                'errors' => $errors,
                'success' => false,
            ]);
        }

        // Find user and update password
        $user = $this->repository->findByEmail($email);

        if ($user === null) {
            $errors['email'] = 'No account found with this email address.';
            error_log('[ResetPassword] User not found for email=' . $email);
            return $this->view->render('pages/auth/reset-password', [
                'token' => $token,
                'errors' => $errors,
                'success' => false,
            ]);
        }

        // Update password
        $user->password = $this->hasher->hash($password);
        $user->updatedAt = date('Y-m-d H:i:s');
        $this->repository->save($user);

        error_log('[ResetPassword] Password updated for user id=' . ($user->id ?? '?') . ', email=' . $email);

        // Delete the token
        $this->resetService->deleteToken($email);

        // Redirect to login with success message
        return Response::redirect('/mark/login?reset=success');
    }
}
