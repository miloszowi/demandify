<?php

declare(strict_types=1);

namespace Demandify\Application\Command\ApproveDemand;

use Demandify\Application\Command\CommandHandler;
use Demandify\Application\Query\IsUserEligibleToDecisionForExternalService\IsUserEligibleToDecisionForExternalService;
use Demandify\Application\Query\QueryBus;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Event\DemandApproved;
use Demandify\Domain\Demand\Exception\UserNotAuthorizedToUpdateDemandException;
use Demandify\Domain\DomainEventPublisher;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ApproveDemandHandler implements CommandHandler
{
    public function __construct(
        private readonly DemandRepository $demandRepository,
        private readonly DomainEventPublisher $domainEventPublisher,
        private readonly QueryBus $queryBus,
    ) {}

    public function __invoke(ApproveDemand $command): void
    {
        $demand = $this->demandRepository->getByUuid($command->demandUuid);
        $isUserEligible = $this->queryBus->ask(
            new IsUserEligibleToDecisionForExternalService($command->approver, $demand->service)
        );

        if (!$isUserEligible) {
            throw UserNotAuthorizedToUpdateDemandException::fromUser($command->approver, $demand->service);
        }

        $demand->approveBy($command->approver);
        $this->demandRepository->save($demand);

        $this->domainEventPublisher->publish(new DemandApproved($demand));
    }
}
