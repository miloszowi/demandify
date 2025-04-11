<?php

declare(strict_types=1);

namespace Demandify\Domain\Demand;

use Demandify\Domain\Demand\Exception\DemandNotFoundException;
use Demandify\Domain\ExternalService\ExternalServiceConfiguration;
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
     * @return array{demands: Demand[], total: int, page: int, limit: int, totalPages: int, search: ?string}
     */
    public function findPaginatedForUser(UuidInterface $uuid, int $page, int $limit, ?string $search = null): array;

    /**
     * @param ExternalServiceConfiguration[] $services
     *
     * @return Demand[]
     */
    public function findDemandsAwaitingDecisionForServices(UuidInterface $userUuid, array $services): array;

    public function save(Demand $demand): void;
}
