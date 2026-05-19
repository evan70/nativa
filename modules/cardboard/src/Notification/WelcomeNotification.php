<?php

declare(strict_types=1);

namespace Marko\Cardboard\Notification;

use Marko\Mail\Message;
use Marko\Notification\Contracts\NotifiableInterface;
use Marko\Notification\Contracts\NotificationInterface;

/**
 * Welcome notification sent to newly registered users.
 *
 * Sends via 'mail' channel in dev (logged to storage/logs/mail.log)
 * and 'database' channel (persisted to notifications table).
 */
readonly class WelcomeNotification implements NotificationInterface
{
    public function __construct() {}

    /**
     * @return array<string>
     */
    public function channels(NotifiableInterface $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(NotifiableInterface $notifiable): Message
    {
        return Message::create()
            ->subject('Welcome to Marko App!')
            ->view('emails/welcome')
            ->with([
                'name' => $notifiable->routeNotificationFor('name') ?? 'User',
                'email' => $notifiable->routeNotificationFor('mail') ?? '',
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(NotifiableInterface $notifiable): array
    {
        return [
            'title' => 'Welcome to Marko App!',
            'body' => 'Thank you for registering. Your account has been created successfully.',
            'type' => 'welcome',
        ];
    }
}
