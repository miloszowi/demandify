<?php

declare(strict_types=1);

namespace Demandify\Application\Query\GetDemandsAwaitingDecisionForUser;

use Demandify\Application\Query\QueryHandler;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetDemandsAwaitingDecisionForUserHandler implements QueryHandler
{
    public function __construct(
        private readonly DemandRepository $demandRepository,
        private readonly ExternalServiceConfigurationRepository $externalServiceConfigurationRepository
    ) {}

    /**
     * @return Demand[]
     */
    public function __invoke(GetDemandsAwaitingDecisionForUser $query): array
    {
        $eligibleServices = $this->externalServiceConfigurationRepository->findEligibleForUser($query->userUuid);

        return $this->demandRepository->findDemandsAwaitingDecisionForServices(
            $query->userUuid,
            $eligibleServices
        );
    }
}
