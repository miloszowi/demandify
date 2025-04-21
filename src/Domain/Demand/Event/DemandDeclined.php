<?php

declare(strict_types=1);

namespace Demandify\Domain\Demand\Event;

use Demandify\Domain\DomainEvent;
use Ramsey\Uuid\UuidInterface;

readonly class DemandDeclined implements DomainEvent
{
    public function __construct(
        public UuidInterface $demandUuid,
        private \DateTimeImmutable $occuredAt = new \DateTimeImmutable(),
    ) {}

    public function occuredAt(): \DateTimeImmutable
    {
        return $this->occuredAt;
    }
}
