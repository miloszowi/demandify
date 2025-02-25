<?php

declare(strict_types=1);

namespace Demandify\Application\Event\DemandApproved;

use Demandify\Application\Command\ExecuteDemand\ExecuteDemand;
use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use Demandify\Domain\Demand\Event\DemandApproved;
use Demandify\Domain\Notification\NotificationRepository;
use Demandify\Domain\Notification\NotificationType;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DemandApprovedHandler
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly NotificationRepository $notificationRepository,
    ) {}

    public function __invoke(DemandApproved $event): void
    {
        $notifications = $this->notificationRepository->findByDemandUuidAndAction($event->demand->uuid, NotificationType::NEW_DEMAND);

        if (!empty($notifications)) {
            $this->messageBus->dispatch(
                new UpdateSentNotificationsWithDecision(
                    $notifications,
                    $event->demand,
                )
            );
        }
        $this->messageBus->dispatch(new ExecuteDemand($event->demand->uuid));
        $this->messageBus->dispatch(
            new SendDemandNotification(
                $event->demand->requester->uuid,
                $event->demand,
                NotificationType::DEMAND_APPROVED
            )
        );
    }
}
