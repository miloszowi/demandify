<?php

declare(strict_types=1);

namespace Demandify\Application\Command\SendDemandNotification;

use Demandify\Application\Command\CommandHandler;
use Demandify\Domain\Notification\NotificationService;
use Demandify\Domain\User\UserRepository;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;

class SendDemandNotificationHandler implements CommandHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly NotificationService $notificationService,
    ) {}

    public function __invoke(SendDemandNotification $command): void
    {
        $recipient = $this->userRepository->getByUuid($command->recipientUuid);

        /** @var UserSocialAccount $socialAccount */
        foreach ($recipient->getSocialAccounts() as $socialAccount) {
            if (false === $socialAccount->isNotifiable()) {
                continue;
            }

            $this->notificationService->send(
                $command->notificationType,
                $command->demand,
                $socialAccount
            );
        }
    }
}
