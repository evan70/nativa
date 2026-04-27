<?php

declare(strict_types=1);

namespace Marko\Notification\Database\Entity;

use Marko\Database\Attributes\Column;
use Marko\Database\Attributes\Table;
use Marko\Database\Entity\Entity;

#[Table('notifications')]
class DatabaseNotification extends Entity
{
    #[Column(primaryKey: true)]
    public string $id = '';

    #[Column]
    public string $type = '';

    #[Column]
    public string $notifiableType = '';

    #[Column]
    public string $notifiableId = '';

    #[Column(type: 'TEXT')]
    public string $data = '';

    #[Column]
    public ?string $readAt = null;

    #[Column]
    public string $createdAt = '';
}
