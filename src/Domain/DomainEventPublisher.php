<?php

declare(strict_types=1);

namespace Demandify\Domain;

interface DomainEventPublisher
{
    public function publish(DomainEvent $event): void;
}
