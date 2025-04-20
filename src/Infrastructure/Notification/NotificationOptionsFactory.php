<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Notification;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\Status;
use Demandify\Domain\Notification\Notification;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\User\User;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;
use Demandify\Infrastructure\Notification\Options\NotificationOptionsFactory as NotificationOptionsFactoryInterface;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;

class NotificationOptionsFactory
{
    public function __construct(
        /** @var NotificationOptionsFactoryInterface[] */
        private readonly iterable $notificationOptionsFactories,
    ) {}

    public function create(
        Demand $demand,
        NotificationType $notificationType,
        UserSocialAccount $userSocialAccount
    ): MessageOptionsInterface {
        foreach ($this->notificationOptionsFactories as $notificationOptionsFactory) {
            if ($notificationOptionsFactory->supports($userSocialAccount->type)) {
                return $notificationOptionsFactory->create(
                    $demand,
                    $notificationType,
                    $userSocialAccount,
                );
            }
        }

        throw new \RuntimeException('Notification options factory not found');
    }

    public function createForDecision(
        Notification $notification,
        User $approver,
        Status $status,
    ): MessageOptionsInterface {
        foreach ($this->notificationOptionsFactories as $notificationOptionsFactory) {
            if ($notificationOptionsFactory->supports($notification->socialAccountType)) {
                return $notificationOptionsFactory->createForDecision($notification, $approver, $status);
            }
        }

        throw new \RuntimeException('Notification options factory not found');
    }
}
