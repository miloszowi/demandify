<?php

declare(strict_types=1);

namespace Querify\Application\Event\DemandApproved;

use Querify\Application\Command\ExecuteDemand\ExecuteDemand;
use Querify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use Querify\Domain\Demand\Event\DemandApproved;
use Querify\Domain\Demand\Status;
use Querify\Domain\Notification\NotificationRepository;
use Querify\Domain\Notification\NotificationService;
use Querify\Domain\Notification\NotificationType;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DemandApprovedHandler
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly NotificationService $notificationService,
        private readonly NotificationRepository $notificationRepository,
    ) {}

    public function __invoke(DemandApproved $event): void
    {
        $this->messageBus->dispatch(new ExecuteDemand($event->demand));
        $this->messageBus->dispatch(
            new UpdateSentNotificationsWithDecision(
                $this->notificationRepository->findByDemandUuidAndAction($event->demand->uuid, NotificationType::NEW_DEMAND),
                $event->demand,
            )
        );
        $this->messageBus->dispatch(

        )

        foreach ($event->demand->requester->getSocialAccounts() as $socialAccount) {
            // todo: maybe some bool on user social account to determine if should communicate through this channel?
            $this->notificationService->sendNotification(
                NotificationType::DEMAND_APPROVED,
                $event->demand,
                $socialAccount
            );
        }
    }
}
