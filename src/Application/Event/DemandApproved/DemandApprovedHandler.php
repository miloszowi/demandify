<?php

declare(strict_types=1);

namespace Querify\Application\Event\DemandApproved;

use Querify\Application\Command\ExecuteDemand\ExecuteDemand;
use Querify\Application\Command\SendDemandNotification\SendDemandNotification;
use Querify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use Querify\Domain\Demand\Event\DemandApproved;
use Querify\Domain\Notification\NotificationRepository;
use Querify\Domain\Notification\NotificationType;
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
