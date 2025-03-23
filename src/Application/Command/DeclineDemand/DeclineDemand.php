<?php

declare(strict_types=1);

namespace Demandify\Application\Command\DeclineDemand;

use Demandify\Application\Command\Command;
use Demandify\Domain\User\User;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
readonly class DeclineDemand implements Command
{
    public function __construct(
        public UuidInterface $demandUuid,
        public User $approver,
    ) {}
}
