<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Notification\Client;

use Querify\Domain\Demand\Demand;
use Querify\Domain\Notification\Notification;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\Notification\Client\Response\SendNotificationResponse;

interface NotificationClient
{
    public const string NEW_DEMAND = 'new_demand';
    public const string DEMAND_APPROVED = 'demand_approved';
    public const string DEMAND_DECLINED = 'demand_declined';

    public function send(string $action, Demand $demand, UserSocialAccount $userSocialAccount): SendNotificationResponse;

    public function update(Notification $notification, Demand $demand, UserSocialAccount $userSocialAccount): void;

    public function getType(): UserSocialAccountType;

    public function supports(UserSocialAccountType $userSocialAccountType): bool;
}
