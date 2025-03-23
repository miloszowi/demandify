<?php

declare(strict_types=1);

namespace Demandify\Application\Command\UpdateSentNotificationsWithDecision;

use Demandify\Application\Command\Command;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Notification\Notification;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
readonly class UpdateSentNotificationsWithDecision implements Command
{
    public function __construct(
        /** @var Notification[] */
        public array $notifications,
        public Demand $demand,
    ) {}
}
