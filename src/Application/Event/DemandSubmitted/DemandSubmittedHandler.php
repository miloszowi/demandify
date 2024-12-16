<?php

declare(strict_types=1);

namespace Querify\Application\Event\DemandSubmitted;

use Querify\Application\Command\SendDemandNotification\SendDemandNotification;
use Querify\Domain\Demand\Event\DemandSubmitted;
use Querify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Querify\Domain\Notification\NotificationType;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DemandSubmittedHandler
{
    public function __construct(
        private readonly ExternalServiceConfigurationRepository $externalServiceConfigurationRepository,
        private readonly MessageBusInterface $messageBus
    ) {}

    public function __invoke(DemandSubmitted $event): void
    {
        $externalServiceConfiguration = $this->externalServiceConfigurationRepository->findByName($event->demand->service);

        if (!$externalServiceConfiguration?->eligibleApprovers) {
            // no eligible approvers specified for this external service
            return;
        }

        foreach ($externalServiceConfiguration->eligibleApprovers as $approverUuid) {
            $this->messageBus->dispatch(
                new SendDemandNotification($approverUuid, $event->demand, NotificationType::NEW_DEMAND)
            );
        }
    }
}
