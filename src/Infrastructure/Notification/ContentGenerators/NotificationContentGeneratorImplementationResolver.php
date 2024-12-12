<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Notification\ContentGenerators;

use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\Notification\NotificationContentGenerator;

class NotificationContentGeneratorImplementationResolver
{
    public function __construct(
        /** @var NotificationContentGenerator[] */
        private readonly iterable $notificationContentGenerators
    ) {}

    public function get(UserSocialAccountType $userSocialAccountType): ?NotificationContentGenerator
    {
        foreach ($this->notificationContentGenerators as $notificationContentGenerator) {
            if ($notificationContentGenerator->supports($userSocialAccountType)) {
                return $notificationContentGenerator;
            }
        }

        return null;
    }
}
