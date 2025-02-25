<?php

declare(strict_types=1);

namespace Demandify\Domain\Notification;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;

interface NotificationService
{
    public function send(NotificationType $notificationType, Demand $demand, UserSocialAccount $userSocialAccount): Notification;

    public function update(Notification $notification, Demand $demand): void;
}
