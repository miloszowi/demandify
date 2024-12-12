<?php

declare(strict_types=1);

namespace Querify\Application\Event\DemandSubmitted;

use Querify\Application\Command\NotifyEligibleApprover\NotifyEligibleApprover;
use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\Demand\Event\DemandSubmitted;
use Querify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DemandSubmittedHandler
{
    public function __construct(
        private readonly ExternalServiceConfigurationRepository $externalServiceConfigurationRepository,
        private readonly DemandRepository $demandRepository,
        private readonly MessageBusInterface $messageBus
    ) {}

    public function __invoke(DemandSubmitted $event): void
    {
        $demand = $this->demandRepository->getByUuid($event->demandUuid);
        $externalServiceConfiguration = $this->externalServiceConfigurationRepository->findByName($demand->service);

        if (null === $externalServiceConfiguration) {
            // no eligible approvers specified for this external service
            return;
        }

        foreach ($externalServiceConfiguration->eligibleApprovers as $approverUuid) {
            $this->messageBus->dispatch(
                new NotifyEligibleApprover($approverUuid, $demand->uuid)
            );
        }
    }
}
