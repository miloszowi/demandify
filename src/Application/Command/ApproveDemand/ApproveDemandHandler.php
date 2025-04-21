<?php

declare(strict_types=1);

namespace Demandify\Application\Command\ApproveDemand;

use Demandify\Application\Command\CommandHandler;
use Demandify\Application\Query\IsUserEligibleToDecisionForExternalService\IsUserEligibleToDecisionForExternalService;
use Demandify\Application\Query\QueryBus;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Exception\UserNotAuthorizedToUpdateDemandException;
use Demandify\Domain\User\UserRepository;

class ApproveDemandHandler implements CommandHandler
{
    public function __construct(
        private readonly DemandRepository $demandRepository,
        private readonly UserRepository $userRepository,
        private readonly QueryBus $queryBus,
    ) {}

    public function __invoke(ApproveDemand $command): void
    {
        $demand = $this->demandRepository->getByUuid($command->demandUuid);
        $approver = $this->userRepository->getByUuid($command->approverUuid);
        $isUserEligible = $this->queryBus->ask(
            new IsUserEligibleToDecisionForExternalService($approver, $demand->service)
        );

        if (!$isUserEligible) {
            throw UserNotAuthorizedToUpdateDemandException::fromUser($approver, $demand->service);
        }

        $demand->approveBy($approver);
        $this->demandRepository->save($demand);
    }
}
