<?php

declare(strict_types=1);

namespace Querify\Domain\Demand;

use Querify\Domain\Demand\Exception\DemandNotFoundException;
use Querify\Domain\User\User;
use Ramsey\Uuid\UuidInterface;

interface DemandRepository
{
    /**
     * @throws DemandNotFoundException
     */
    public function getByUuid(UuidInterface $uuid): Demand;

    public function findByUuid(UuidInterface $uuid): Demand;

    /**
     * @return Demand[]
     */
    public function findAllFromUser(User $user): array;

    public function save(Demand $demand): void;
}
