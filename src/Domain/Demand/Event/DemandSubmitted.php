<?php

declare(strict_types=1);

namespace Demandify\Domain\Demand\Event;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\DomainEvent;

readonly class DemandSubmitted implements DomainEvent
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
