<?php

declare(strict_types=1);

namespace Querify\Application\Command\SendDemandNotification;

use Querify\Domain\Demand\Demand;
use Querify\Domain\Notification\NotificationType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
readonly class SendDemandNotification
{
    public function __construct(
        public UuidInterface $recipientUuid,
        public Demand $demand,
        public NotificationType $notificationType,
    ) {}
}
