<?php

declare(strict_types=1);

namespace Demandify\Domain;

interface EventReleasable
{
    /**
     * @return DomainEvent[]
     */
    public function releaseEvents(): array;
}
