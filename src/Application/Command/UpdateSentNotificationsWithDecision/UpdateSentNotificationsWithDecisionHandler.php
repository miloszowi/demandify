<?php

declare(strict_types=1);

namespace Demandify\Application\Command\UpdateSentNotificationsWithDecision;

use Demandify\Application\Command\CommandHandler;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Notification\NotificationRepository;
use Demandify\Domain\Notification\NotificationService;
use Demandify\Domain\Notification\NotificationType;

class UpdateSentNotificationsWithDecisionHandler implements CommandHandler
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly DemandRepository $demandRepository,
        private readonly NotificationRepository $notificationRepository,
    ) {}

    public function __invoke(UpdateSentNotificationsWithDecision $command): void
    {
        $demand = $this->demandRepository->getByUuid($command->demandUuid);
        $notifications = $this->notificationRepository->findByDemandAndType(
            $command->demandUuid,
            NotificationType::NEW_DEMAND
        );

        foreach ($notifications as $notification) {
            $this->notificationService->updateWithDecision($notification, $demand);
        }
    }
}
