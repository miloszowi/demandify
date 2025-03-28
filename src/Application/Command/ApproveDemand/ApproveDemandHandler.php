<?php

declare(strict_types=1);

namespace Demandify\Application\Command\ApproveDemand;

use Demandify\Application\Command\CommandHandler;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Event\DemandApproved;
use Demandify\Domain\Demand\Exception\UserNotAuthorizedToUpdateDemandException;
use Demandify\Domain\DomainEventPublisher;
use Demandify\Domain\ExternalService\ExternalServiceConfigurationRepository;

class ApproveDemandHandler implements CommandHandler
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

        $this->domainEventPublisher->publish(new DemandApproved($demand));
    }
}
