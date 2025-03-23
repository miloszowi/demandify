<?php

declare(strict_types=1);

namespace Demandify\Application\Command\ApproveDemand;

use Demandify\Application\Command\Command;
use Demandify\Domain\User\User;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
readonly class ApproveDemand implements Command
{
    public function __construct(
        public UuidInterface $demandUuid,
        public User $approver,
    ) {}
}
