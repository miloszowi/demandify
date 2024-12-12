<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Notification;

use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\Notification\Clients\Response\SendNotificationResponse;

interface NotificationClient
{
    public function send(string $content, array $attachments, UserSocialAccount $userSocialAccount): SendNotificationResponse;

    public function getType(): UserSocialAccountType;

    public function supports(UserSocialAccountType $userSocialAccountType): bool;
}
