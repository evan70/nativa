<?php

declare(strict_types=1);

namespace Marko\Mark\Events;

use DateTimeImmutable;
use Marko\Mark\Entity\MarkInterface;
use Marko\Core\Event\Event;

class MarkCreated extends Event
{
    public function __construct(
        private readonly MarkInterface $user,
        private readonly DateTimeImmutable $timestamp = new DateTimeImmutable(),
    ) {}

    public function getUser(): MarkInterface
    {
        return $this->user;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }
}
