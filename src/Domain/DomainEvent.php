<?php

declare(strict_types=1);

namespace Demandify\Domain;

interface DomainEvent
{
    public function occuredAt(): \DateTimeImmutable;
}
