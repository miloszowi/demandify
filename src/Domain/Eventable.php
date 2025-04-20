<?php

declare(strict_types=1);

namespace Demandify\Domain;

trait Eventable
{
    /** @var DomainEvent[] */
    private array $events = [];

    final public function recordThat(DomainEvent $event): void
    {
        $this->events[] = $event;
    }

    final public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }
}
