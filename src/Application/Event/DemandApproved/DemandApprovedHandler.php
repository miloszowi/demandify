<?php

declare(strict_types=1);

namespace Demandify\Application\Event\DemandApproved;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Command\ExecuteDemand\ExecuteDemand;
use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use Demandify\Application\Event\DomainEventHandler;
use Demandify\Domain\Demand\Event\DemandApproved;
use Demandify\Domain\Notification\NotificationRepository;
use Demandify\Domain\Notification\NotificationType;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DemandApprovedHandler implements DomainEventHandler
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly NotificationRepository $notificationRepository,
    ) {}

    public function __invoke(DemandApproved $event): void
    {
        $notifications = $this->notificationRepository->findByDemandUuidAndAction($event->demand->uuid, NotificationType::NEW_DEMAND);

        if (!empty($notifications)) {
            $this->commandBus->dispatch(
                new UpdateSentNotificationsWithDecision(
                    $notifications,
                    $event->demand,
                )
            );
        }
        $this->commandBus->dispatch(new ExecuteDemand($event->demand->uuid));
        $this->commandBus->dispatch(
            new SendDemandNotification(
                $event->demand->requester->uuid,
                $event->demand,
                NotificationType::DEMAND_APPROVED
            )
        );
    }
}
