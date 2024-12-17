<?php

declare(strict_types=1);

namespace Querify\Application\Event\DemandDeclined;

use Querify\Application\Command\SendDemandNotification\SendDemandNotification;
use Querify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use Querify\Domain\Demand\Event\DemandDeclined;
use Querify\Domain\Notification\NotificationRepository;
use Querify\Domain\Notification\NotificationType;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DemandDeclinedHandler
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly NotificationRepository $notificationRepository,
    ) {}

    public function __invoke(DemandDeclined $event): void
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

        $this->messageBus->dispatch(
            new SendDemandNotification(
                $event->demand->requester->uuid,
                $event->demand,
                NotificationType::DEMAND_DECLINED
            )
        );
    }
}
