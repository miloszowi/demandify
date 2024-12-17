<?php

declare(strict_types=1);

namespace Querify\Application\Command\ApproveDemand;

use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\Demand\Event\DemandApproved;
use Querify\Domain\Demand\Exception\UserNotAuthorizedToUpdateDemandException;
use Querify\Domain\DomainEventPublisher;
use Querify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ApproveDemandHandler
{
    public function __construct(
        private readonly DemandRepository $demandRepository,
        private readonly DomainEventPublisher $domainEventPublisher,
        private readonly ExternalServiceConfigurationRepository $externalServiceConfigurationRepository
    ) {}

    public function __invoke(ApproveDemand $command): void
    {
        $demand = $this->demandRepository->getByUuid($command->demandUuid);
        $externalServiceConfiguration = $this->externalServiceConfigurationRepository->getByName($demand->service);

        if (!$externalServiceConfiguration->isUserEligible($command->approver)) {
            throw UserNotAuthorizedToUpdateDemandException::fromUser($command->approver, $demand->service);
        }

        $demand->approveBy($command->approver);
        $this->demandRepository->save($demand);

        $this->domainEventPublisher->publish(
            new DemandApproved($demand)
        );
    }
}
