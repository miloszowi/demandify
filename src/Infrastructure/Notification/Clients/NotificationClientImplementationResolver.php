<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Notification\Clients;

use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\Notification\NotificationClient;

class NotificationClientImplementationResolver
{
    public function __construct(
        /** @var NotificationClient[] */
        private readonly iterable $notificationClients,
    ) {}

    public function get(UserSocialAccountType $userSocialAccountType): ?NotificationClient
    {
        foreach ($this->notificationClients as $notificationClient) {
            if ($notificationClient->supports($userSocialAccountType)) {
                return $notificationClient;
            }
        }

        return null;
    }
}
