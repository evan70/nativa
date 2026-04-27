<?php

declare(strict_types=1);

namespace Marko\Notification\Database\Repository;

use Marko\Database\Repository\Repository;
use Marko\Notification\Contracts\NotifiableInterface;
use Marko\Notification\Database\Entity\DatabaseNotification;
use DateTimeImmutable;

class DatabaseNotificationRepository extends Repository implements NotificationRepositoryInterface
{
    protected const string ENTITY_CLASS = DatabaseNotification::class;

    public function forNotifiable(NotifiableInterface $notifiable): array
    {
        return $this->query()
            ->where('notifiableType', '=', $notifiable::class)
            ->where('notifiableId', '=', $this->getNotifiableId($notifiable))
            ->orderBy('createdAt', 'DESC')
            ->getEntities();
    }

    public function unread(NotifiableInterface $notifiable): array
    {
        return $this->query()
            ->where('notifiableType', '=', $notifiable::class)
            ->where('notifiableId', '=', $this->getNotifiableId($notifiable))
            ->whereNull('readAt')
            ->orderBy('createdAt', 'DESC')
            ->getEntities();
    }

    public function markAsRead(string $notificationId): void
    {
        $this->connection->execute(
            "UPDATE {$this->metadata->tableName} SET readAt = ? WHERE id = ?",
            [(new DateTimeImmutable())->format('Y-m-d H:i:s'), $notificationId]
        );
    }

    public function markAllAsRead(NotifiableInterface $notifiable): void
    {
        $this->connection->execute(
            "UPDATE {$this->metadata->tableName} SET readAt = ? WHERE notifiableType = ? AND notifiableId = ? AND readAt IS NULL",
            [
                (new DateTimeImmutable())->format('Y-m-d H:i:s'),
                $notifiable::class,
                $this->getNotifiableId($notifiable)
            ]
        );
    }

    public function delete(string $notificationId): void
    {
        $this->connection->execute(
            "DELETE FROM {$this->metadata->tableName} WHERE id = ?",
            [$notificationId]
        );
    }

    public function deleteAll(NotifiableInterface $notifiable): void
    {
        $this->connection->execute(
            "DELETE FROM {$this->metadata->tableName} WHERE notifiableType = ? AND notifiableId = ?",
            [
                $notifiable::class,
                $this->getNotifiableId($notifiable)
            ]
        );
    }

    public function unreadCount(NotifiableInterface $notifiable): int
    {
        $result = $this->connection->query(
            "SELECT COUNT(*) as count FROM {$this->metadata->tableName} WHERE notifiableType = ? AND notifiableId = ? AND readAt IS NULL",
            [
                $notifiable::class,
                $this->getNotifiableId($notifiable)
            ]
        );

        return (int) ($result[0]['count'] ?? 0);
    }

    public function deleteOld(DateTimeImmutable $before): void
    {
        $this->connection->execute(
            "DELETE FROM {$this->metadata->tableName} WHERE createdAt < ?",
            [$before->format('Y-m-d H:i:s')]
        );
    }

    protected function getNotifiableId(NotifiableInterface $notifiable): string
    {
        if (method_exists($notifiable, 'getAuthIdentifier')) {
            return (string) $notifiable->getAuthIdentifier();
        }

        if (isset($notifiable->id)) {
            return (string) $notifiable->id;
        }

        if (method_exists($notifiable, 'getId')) {
            return (string) $notifiable->getId();
        }

        return '';
    }
}
