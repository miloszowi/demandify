<?php

declare(strict_types=1);

namespace Querify\Domain;

interface DomainEvent
{
    public function occuredAt(): \DateTimeImmutable;
}
