<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Notification\Client;

use Querify\Domain\Demand\Demand;
use Querify\Domain\Notification\Notification;
use Querify\Domain\Notification\NotificationType;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\Notification\Client\Response\SendNotificationResponse;

interface NotificationClient
{
    public function send(NotificationType $notificationType, Demand $demand, UserSocialAccount $userSocialAccount): SendNotificationResponse;

    public function update(Notification $notification, Demand $demand): void;

    public function getType(): UserSocialAccountType;

    public function supports(UserSocialAccountType $userSocialAccountType): bool;
}
