<?php

declare(strict_types=1);

namespace Marko\Cardboard\Controller;

use Marko\Authentication\Contracts\GuardInterface;
use Marko\Authentication\Contracts\PasswordHasherInterface;
use Marko\Cardboard\Notification\WelcomeNotification;
use Marko\Mark\Entity\Mark;
use Marko\Mark\Repository\MarkRepositoryInterface;
use Marko\Notification\NotificationSender;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Post;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;

class RegisterController
{
    /**
     * @param MarkRepositoryInterface<Mark> $repository
     */
    public function __construct(
        private readonly ViewInterface $view,
        private readonly MarkRepositoryInterface $repository,
        private readonly GuardInterface $guard,
        private readonly PasswordHasherInterface $hasher,
        private readonly ?NotificationSender $notificationSender = null,
    ) {}

    #[Get(path: '/mark/register')]
    public function showRegistrationForm(
        Request $request,
    ): Response {
        // Already logged in — redirect to dashboard
        if ($this->guard->check()) {
            return Response::redirect('/mark');
        }

        return $this->view->render('pages/auth/register', [
            'old' => [],
            'errors' => [],
        ]);
    }

    #[Post(path: '/mark/register')]
    public function register(
        Request $request,
    ): Response {
        // Already logged in — redirect
        if ($this->guard->check()) {
            return Response::redirect('/mark');
        }

        $nameRaw = $request->post('name', '');
        $name = is_string($nameRaw) ? trim($nameRaw) : '';
        $emailRaw = $request->post('email', '');
        $email = is_string($emailRaw) ? trim($emailRaw) : '';
        $passwordRaw = $request->post('password', '');
        $password = is_string($passwordRaw) ? $passwordRaw : '';
        $passwordConfirmationRaw = $request->post('password_confirmation', '');
        $passwordConfirmation = is_string($passwordConfirmationRaw) ? $passwordConfirmationRaw : '';

        // --- Validation ---
        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        } else {
            // Check uniqueness
            $existing = $this->repository->findByEmail($email);
            if ($existing !== null) {
                $errors['email'] = 'An account with this email already exists.';
            }
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if ($passwordConfirmation === '') {
            $errors['password_confirmation'] = 'Please confirm your password.';
        } elseif ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'Passwords do not match.';
        }

        // Validation failed — re-render form with errors
        if ($errors !== []) {
            return $this->view->render('pages/auth/register', [
                'old' => [
                    'name' => $name,
                    'email' => $email,
                ],
                'errors' => $errors,
            ]);
        }

        // --- Create user ---
        $user = new Mark();
        $user->email = $email;
        $user->password = $this->hasher->hash($password);
        $user->name = $name;
        $user->isActive = '1';
        $user->createdAt = date('Y-m-d H:i:s');
        $user->updatedAt = date('Y-m-d H:i:s');

        $this->repository->save($user);

        error_log('[Register] New user created: id=' . ($user->id ?? '?') . ', email=' . $email);

        // Send welcome notification if the notification system is available
        if ($this->notificationSender !== null) {
            try {
                $this->notificationSender->send(
                    $user,
                    new WelcomeNotification(),
                );
                error_log('[Register] Welcome notification sent for user: ' . $email);
            } catch (\Throwable $e) {
                error_log('[Register] Failed to send welcome notification: ' . $e->getMessage());
            }
        }

        // Auto-login the new user
        $this->guard->login($user);

        return Response::redirect('/mark');
    }
}
