<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Notification;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Notification\Notification;
use Demandify\Domain\Notification\NotificationService as NotificationServiceInterface;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;

class NotificationService implements NotificationServiceInterface
{
    public function __construct(private readonly NotificationClientResolver $notificationClientImplementationResolver) {}

    public function send(NotificationType $notificationType, Demand $demand, UserSocialAccount $userSocialAccount): Notification
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
