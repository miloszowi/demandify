<?php

declare(strict_types=1);

namespace Demandify\Application\Command\UpdateSentNotificationsWithDecision;

use Demandify\Application\Command\CommandHandler;
use Demandify\Domain\Notification\NotificationRepository;
use Demandify\Domain\Notification\NotificationService;
use Demandify\Domain\Notification\NotificationType;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateSentNotificationsWithDecisionHandler implements CommandHandler
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly NotificationRepository $notificationRepository,
    ) {}

    public function __invoke(UpdateSentNotificationsWithDecision $command): void
    {
        $notifications = $this->notificationRepository->findByDemandAndType(
            $command->demand->uuid,
            NotificationType::NEW_DEMAND
        );

        foreach ($notifications as $notification) {
            $this->notificationService->updateWithDecision($notification, $command->demand);
        }
    }
}
