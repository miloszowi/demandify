<?php

declare(strict_types=1);

namespace Demandify\Application\Event\DemandSubmitted;

use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Domain\Demand\Event\DemandSubmitted;
use Demandify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Demandify\Domain\Notification\NotificationType;
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
