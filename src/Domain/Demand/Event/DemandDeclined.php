<?php

declare(strict_types=1);

namespace Querify\Domain\Demand\Event;

use Querify\Domain\Demand\Demand;
use Querify\Domain\DomainEvent;

readonly class DemandDeclined implements DomainEvent
{
    public function __construct(
        public Demand $demand,
        private \DateTimeImmutable $occuredAt = new \DateTimeImmutable(),
    ) {}

    public function occuredAt(): \DateTimeImmutable
    {
        return $this->occuredAt;
    }
}
