<?php

declare(strict_types=1);

namespace Demandify\Application\Command\SendDemandNotification;

use Demandify\Application\Command\CommandHandler;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Notification\NotificationService;
use Demandify\Domain\User\UserRepository;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;

class SendDemandNotificationHandler implements CommandHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly DemandRepository $demandRepository,
        private readonly NotificationService $notificationService,
    ) {}

    public function __invoke(SendDemandNotification $command): void
    {
        $recipient = $this->userRepository->getByUuid($command->recipientUuid);
        $demand = $this->demandRepository->getByUuid($command->demandUuid);

        /** @var UserSocialAccount $socialAccount */
        foreach ($recipient->getSocialAccounts() as $socialAccount) {
            if (false === $socialAccount->isNotifiable()) {
                continue;
            }

            $this->notificationService->send(
                $command->notificationType,
                $demand,
                $socialAccount
            );
        }
    }
}
