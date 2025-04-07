<?php

declare(strict_types=1);

namespace Demandify\Application\Command\UpdateSentNotificationsWithDecision;

use Demandify\Application\Command\CommandHandler;
use Demandify\Domain\Notification\NotificationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateSentNotificationsWithDecisionHandler implements CommandHandler
{
    public function __construct(private readonly NotificationService $notificationService) {}

    public function __invoke(UpdateSentNotificationsWithDecision $command): void
    {
        foreach ($command->notifications as $notification) {
            $this->notificationService->update($notification, $command->demand);
        }
    }
}
