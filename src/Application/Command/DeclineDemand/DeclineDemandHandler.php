<?php

declare(strict_types=1);

namespace Querify\Application\Command\DeclineDemand;

use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\Demand\Event\DemandDeclined;
use Querify\Domain\Demand\Exception\UserNotAuthorizedToUpdateDemandException;
use Querify\Domain\DomainEventPublisher;
use Querify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeclineDemandHandler
{
    public function __construct(
        private readonly DemandRepository $demandRepository,
        private readonly DomainEventPublisher $domainEventPublisher,
        private readonly ExternalServiceConfigurationRepository $externalServiceConfigurationRepository
    ) {}

    public function __invoke(DeclineDemand $command): void
    {
        $demand = $this->demandRepository->getByUuid($command->demandUuid);
        $externalServiceConfiguration = $this->externalServiceConfigurationRepository->getByName($demand->service);

        if (!$externalServiceConfiguration->isUserEligible($command->approver)) {
            throw UserNotAuthorizedToUpdateDemandException::fromUser($command->approver, $demand->service);
        }

        $demand->declineBy($command->approver);
        $this->demandRepository->save($demand);

        $this->domainEventPublisher->publish(
            new DemandDeclined($demand)
        );
    }
}
