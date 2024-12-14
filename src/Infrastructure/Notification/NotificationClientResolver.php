<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Notification;

use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\Notification\Client\Exception\NotificationClientNotImplementedException;
use Querify\Infrastructure\Notification\Client\NotificationClient;

class NotificationClientResolver
{
    public function __construct(
        /** @var NotificationClient[] */
        private readonly iterable $notificationClients,
    ) {}

    /**
     * @throws NotificationClientNotImplementedException
     */
    public function get(UserSocialAccountType $userSocialAccountType): NotificationClient
    {
        foreach ($this->notificationClients as $notificationClient) {
            if ($notificationClient->supports($userSocialAccountType)) {
                return $notificationClient;
            }
        }

        throw NotificationClientNotImplementedException::fromUserSocialAccountType($userSocialAccountType);
    }
}
