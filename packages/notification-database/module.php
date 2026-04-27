<?php

declare(strict_types=1);

use Marko\Notification\Database\Repository\NotificationRepositoryInterface;
use Marko\Notification\Database\Repository\DatabaseNotificationRepository;

return [
    'bindings' => [
        NotificationRepositoryInterface::class => DatabaseNotificationRepository::class,
    ],
];
