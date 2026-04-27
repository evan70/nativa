# Database Notification Storage

Database notification storage for the Marko framework. Persist, query, and manage notification read state in the database.

Provides the `DatabaseNotification` entity, `NotificationRepositoryInterface`, and a `DatabaseNotificationRepository` implementation.

## Installation

```bash
composer require marko/notification-database
```

## Usage

### Querying Notifications

Inject the repository to fetch notifications for a notifiable entity:

```php
use Marko\Notification\Database\Repository\NotificationRepositoryInterface;

class NotificationController
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
    ) {}

    public function index(
        User $user,
    ): array {
        return $this->notificationRepository->forNotifiable($user);
    }

    public function unreadCount(
        User $user,
    ): int {
        return $this->notificationRepository->unreadCount($user);
    }
}
```

### Marking as Read

```php
// Mark one notification as read
$this->notificationRepository->markAsRead($notificationId);

// Mark all notifications as read for a user
$this->notificationRepository->markAllAsRead($user);
```

### Fetching Unread Notifications

```php
$unread = $this->notificationRepository->unread($user);

foreach ($unread as $notification) {
    $data = json_decode($notification->data, true);
    // Process notification data
}
```

### Deleting Notifications

```php
// Delete a single notification
$this->notificationRepository->deleteById($notificationId);

// Delete all notifications for a user
$this->notificationRepository->deleteAll($user);
```
