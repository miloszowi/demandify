<?php

declare(strict_types=1);

namespace Demandify\Domain\Demand;

use Demandify\Domain\Demand\Exception\DemandNotFoundException;
use Demandify\Domain\User\User;
use Ramsey\Uuid\UuidInterface;

interface DemandRepository
{
    /**
     * @throws DemandNotFoundException
     */
    public function getByUuid(UuidInterface $uuid): Demand;

    public function findByUuid(UuidInterface $uuid): ?Demand;

    /**
     * @return Demand[]
     */
    public function findAllFromUser(User $user): array;

    /**
     * @return Demand[]
     */
    public function findInStatus(Status $status): array;

    /**
     * @return Demand[]
     */
    public function getPaginatedResultForUser(UuidInterface $uuid, int $page, int $limit): iterable;

    public function save(Demand $demand): void;
}
