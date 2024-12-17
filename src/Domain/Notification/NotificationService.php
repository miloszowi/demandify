<?php

declare(strict_types=1);

namespace Querify\Domain\Notification;

use Querify\Domain\Demand\Demand;
use Querify\Domain\UserSocialAccount\UserSocialAccount;

interface NotificationService
{
    public function send(NotificationType $notificationType, Demand $demand, UserSocialAccount $userSocialAccount): Notification;

    public function update(Notification $notification, Demand $demand): void;
}
