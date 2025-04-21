<?php

declare(strict_types=1);

namespace Demandify\Application\Command\DeclineDemand;

use Demandify\Application\Command\CommandHandler;
use Demandify\Application\Query\IsUserEligibleToDecisionForExternalService\IsUserEligibleToDecisionForExternalService;
use Demandify\Application\Query\QueryBus;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Exception\UserNotAuthorizedToUpdateDemandException;

class DeclineDemandHandler implements CommandHandler
{
    public function __construct(
        private readonly DemandRepository $demandRepository,
        private readonly QueryBus $queryBus,
    ) {}

    public function __invoke(DeclineDemand $command): void
    {
        $demand = $this->demandRepository->getByUuid($command->demandUuid);
        $isUserEligible = $this->queryBus->ask(
            new IsUserEligibleToDecisionForExternalService($command->approver, $demand->service)
        );

        if (false === $isUserEligible) {
            throw UserNotAuthorizedToUpdateDemandException::fromUser($command->approver, $demand->service);
        }

        $demand->declineBy($command->approver);
        $this->demandRepository->save($demand);
    }
}
