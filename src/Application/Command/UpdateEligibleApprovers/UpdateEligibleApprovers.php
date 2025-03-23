<?php

declare(strict_types=1);

namespace Demandify\Application\Command\UpdateEligibleApprovers;

use Demandify\Application\Command\Command;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
readonly class UpdateEligibleApprovers implements Command
{
    public function __construct(
        public string $externalServiceName,
        /**
         * @var UuidInterface[]
         */
        public array $desiredUserUuidsState
    ) {}
}
