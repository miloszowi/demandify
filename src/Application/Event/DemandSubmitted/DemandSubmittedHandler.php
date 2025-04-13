<?php

declare(strict_types=1);

namespace Demandify\Application\Event\DemandSubmitted;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Event\DomainEventHandler;
use Demandify\Domain\Demand\Event\DemandSubmitted;
use Demandify\Domain\ExternalService\Exception\ExternalServiceConfigurationNotFoundException;
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
        try {
            $externalServiceConfiguration = $this->externalServiceConfigurationRepository->getByName($event->demand->service);
        } catch (ExternalServiceConfigurationNotFoundException) {
            // This is expected behavior. External service configuration may not exist,
            // which means there are no eligible approvers to notify, so we simply return.
            return;
        }

        if (false === $externalServiceConfiguration->hasEligibleApprovers()) {
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
