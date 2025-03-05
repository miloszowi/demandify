<?php

declare(strict_types=1);

namespace Demandify\Application\Command\SendDemandNotification;

use Demandify\Domain\Notification\NotificationRepository;
use Demandify\Domain\Notification\NotificationService;
use Demandify\Domain\User\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendDemandNotificationHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly NotificationService $notificationService,
        private readonly NotificationRepository $notificationRepository,
    ) {}

    public function __invoke(SendDemandNotification $command): void
    {
        $recipient = $this->userRepository->getByUuid($command->recipientUuid);

        foreach ($recipient->getSocialAccounts() as $socialAccount) {
            // todo: maybe some bool on user social account to determine if should communicate through this channel?
            // todo: maybe instead of notification service each social account type should create its own command e.g SendSlackNotification
            $notification = $this->notificationService->send(
                $command->notificationType,
                $command->demand,
                $socialAccount
            );

            $this->notificationRepository->save($notification);
        }
    }
}
