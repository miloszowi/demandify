<?php

declare(strict_types=1);

namespace Demandify\Application\Command\SaveSentNotification;

use Demandify\Application\Command\CommandHandler;
use Demandify\Domain\Notification\NotificationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SaveSentNotificationHandler implements CommandHandler
{
    public function __construct(private readonly NotificationRepository $notificationRepository) {}

    public function __invoke(SaveSentNotification $saveSentNotification): void
    {
        $this->notificationRepository->save($saveSentNotification->toNotification());
    }
}
