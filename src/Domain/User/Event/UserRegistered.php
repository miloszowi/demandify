<?php

declare(strict_types=1);

namespace Demandify\Domain\User\Event;

use Demandify\Domain\DomainEvent;
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
