<?php

declare(strict_types=1);

namespace Querify\Domain\User\Event;

use Querify\Domain\DomainEvent;
use Ramsey\Uuid\UuidInterface;

final readonly class UserRegistered implements DomainEvent
{
    public function __construct(
        public UuidInterface $userUuid,
        public \DateTimeImmutable $occuredAt = new \DateTimeImmutable(),
    ) {}

    public function occuredAt(): \DateTimeImmutable
    {
        return $this->occuredAt;
    }
}
