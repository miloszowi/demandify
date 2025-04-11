<?php

declare(strict_types=1);

namespace Demandify\Application\Query\GetDemandsSubmittedByUser;

use Demandify\Application\Query\QueryHandler;
use Demandify\Application\Query\ReadModel\DemandsSubmittedByUser;
use Demandify\Domain\Demand\DemandRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetDemandsSubmittedByUserHandler implements QueryHandler
{
    public function __construct(private readonly DemandRepository $demandRepository) {}

    public function __invoke(GetDemandsSubmittedByUser $query): DemandsSubmittedByUser
    {
        $repositoryOutput = $this->demandRepository->findPaginatedForUser(
            $query->userUuid,
            $query->page,
            $query->limit,
            $query->search
        );

        return new DemandsSubmittedByUser(
            $repositoryOutput['demands'],
            $repositoryOutput['total'],
            $repositoryOutput['page'],
            $repositoryOutput['limit'],
            $repositoryOutput['totalPages'],
            $repositoryOutput['search'],
        );
    }
}
