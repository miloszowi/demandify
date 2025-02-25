<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Notification\Client;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Notification\Notification;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Notification\Client\Response\SendNotificationResponse;

interface NotificationClient
{
    public function send(NotificationType $notificationType, Demand $demand, UserSocialAccount $userSocialAccount): SendNotificationResponse;

    public function update(Notification $notification, Demand $demand): void;

    public function getType(): UserSocialAccountType;

    public function supports(UserSocialAccountType $userSocialAccountType): bool;
}
