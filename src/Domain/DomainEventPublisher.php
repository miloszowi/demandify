<?php

declare(strict_types=1);

namespace Querify\Domain;

interface DomainEventPublisher
{
    public function publish(DomainEvent $event): void;
}
