<?php

declare(strict_types=1);

namespace Demandify\Application\Command\UpdateSentNotificationsWithDecision;

use Demandify\Application\Command\Command;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
readonly class UpdateSentNotificationsWithDecision implements Command
{
    public function __construct(public UuidInterface $demandUuid) {}
}
