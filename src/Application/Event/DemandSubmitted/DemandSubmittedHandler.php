<?php

declare(strict_types=1);

namespace Demandify\Application\Event\DemandSubmitted;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Event\DomainEventHandler;
use Demandify\Domain\Demand\Event\DemandSubmitted;
use Demandify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Demandify\Domain\Notification\NotificationType;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DemandSubmittedHandler implements DomainEventHandler
{
    public function __construct(
        private readonly ExternalServiceConfigurationRepository $externalServiceConfigurationRepository,
        private readonly CommandBus $commandBus
    ) {}

    public function __invoke(DemandSubmitted $event): void
    {
        $externalServiceConfiguration = $this->externalServiceConfigurationRepository->findByName($event->demand->service);

        if (!$externalServiceConfiguration?->eligibleApprovers) {
            // no eligible approvers specified for this external service
            return;
        }

        foreach ($externalServiceConfiguration->eligibleApprovers as $approverUuid) {
            $this->commandBus->dispatch(
                new SendDemandNotification(
                    Uuid::fromString($approverUuid),
                    $event->demand,
                    NotificationType::NEW_DEMAND
                )
            );
        }
    }
}
