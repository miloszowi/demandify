<?php

declare(strict_types=1);

namespace Querify\Domain\Notification;

use Querify\Domain\Demand\Demand;
use Querify\Domain\Task\Task;
use Querify\Domain\UserSocialAccount\UserSocialAccount;

interface NotificationService
{
    public function notifyAboutNewDemand(UserSocialAccount $socialAccount, Demand $demand): void;

    public function notifyDemandDecisionMade(UserSocialAccount $socialAccount, Demand $demand): void;

    public function notifyAboutNewTask(UserSocialAccount $socialAccount, Task $task): void;
}
