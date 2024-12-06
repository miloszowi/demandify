<?php

declare(strict_types=1);

namespace Querify\Domain\Demand;

use Querify\Domain\Demand\Exception\DemandNotFoundException;
use Ramsey\Uuid\UuidInterface;

interface DemandRepository
{
    /**
     * @throws DemandNotFoundException
     */
    public function getByUuid(UuidInterface $uuid): Demand;

    public function findByUuid(UuidInterface $uuid): Demand;

    public function save(Demand $demand): void;
}
