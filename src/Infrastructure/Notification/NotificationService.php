<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Notification;

use Querify\Domain\Demand\Demand;
use Querify\Domain\Notification\Notification;
use Querify\Domain\Notification\NotificationService as NotificationServiceInterface;
use Querify\Domain\Notification\NotificationType;
use Querify\Domain\Task\Task;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Infrastructure\Notification\Client\NotificationClient;

class NotificationService implements NotificationServiceInterface
{
    public function __construct(private readonly NotificationClientResolver $notificationClientImplementationResolver) {}

    public function sendNotification(NotificationType $notificationType, Demand $demand, UserSocialAccount $userSocialAccount): Notification
    {
        $notificationClient = $this->notificationClientImplementationResolver->get($userSocialAccount->type);

        $notificationResponse = $notificationClient->send(
            $notificationType,
            $demand,
            $userSocialAccount
        );

        return new Notification(
            $demand->uuid,
            $notificationType,
            $notificationResponse->notificationIdentifier,
            $notificationResponse->content,
            $notificationResponse->attachments,
            $notificationResponse->channel,
            $userSocialAccount->type,
        );
    }

    public function update(Notification $notification, Demand $demand): void
    {
        $notificationClient = $this->notificationClientImplementationResolver->get($notification->socialAccountType);

        $notificationClient->update($notification, $demand);
    }
}
