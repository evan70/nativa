<?php

declare(strict_types=1);

namespace Marko\Notification\Database\Repository;

use Marko\Notification\Contracts\NotifiableInterface;
use Marko\Notification\Database\Entity\DatabaseNotification;

interface NotificationRepositoryInterface
{
    /**
     * @return array<DatabaseNotification>
     */
    public function forNotifiable(NotifiableInterface $notifiable): array;

    /**
     * @return array<DatabaseNotification>
     */
    public function unread(NotifiableInterface $notifiable): array;

    public function markAsRead(string $notificationId): void;

    public function markAllAsRead(NotifiableInterface $notifiable): void;

    public function deleteById(string $notificationId): void;

    public function deleteAll(NotifiableInterface $notifiable): void;

    public function unreadCount(NotifiableInterface $notifiable): int;

    public function deleteOld(\DateTimeImmutable $before): void;
}
