<?php

declare(strict_types=1);

namespace Demandify\Application\Command\SendDemandNotification;

use Demandify\Application\Command\Command;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Notification\NotificationType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
readonly class SendDemandNotification implements Command
{
    public function __construct(
        public UuidInterface $recipientUuid,
        public Demand $demand,
        public NotificationType $notificationType,
    ) {}
}
