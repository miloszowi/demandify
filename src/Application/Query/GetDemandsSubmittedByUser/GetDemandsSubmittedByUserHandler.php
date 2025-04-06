<?php

declare(strict_types=1);

namespace Demandify\Application\Query\GetDemandsSubmittedByUser;

use Demandify\Application\Query\QueryHandler;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\DemandRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetDemandsSubmittedByUserHandler implements QueryHandler
{
    public function __construct(private readonly DemandRepository $demandRepository) {}

    /**
     * @return array{demands: Demand[], total: int, page: int, limit: int, totalPages: int, search: ?string}
     */
    public function __invoke(GetDemandsSubmittedByUser $query): array
    {
        return $this->demandRepository->findPaginatedForUser(
            $query->userUuid,
            $query->page,
            $query->limit,
            $query->search
        );
    }
}
