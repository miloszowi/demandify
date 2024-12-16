<?php

declare(strict_types=1);

namespace Querify\Application\Command\UpdateSentNotificationsWithDecision;

use Querify\Domain\Demand\Demand;
use Querify\Domain\Notification\Notification;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
readonly class UpdateSentNotificationsWithDecision
{
    public function __construct(
        /** @var Notification[] */
        public array $notifications,
        public Demand $demand,
    ) {}
}
