<?php

declare(strict_types=1);

namespace Querify\Domain\Demand;

use Ramsey\Uuid\UuidInterface;

interface DemandRepository
{
    public function findByUuid(UuidInterface $uuid): Demand;

    public function save(Demand $demand): void;
}