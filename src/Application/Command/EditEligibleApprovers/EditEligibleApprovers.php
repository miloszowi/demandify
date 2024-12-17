<?php

declare(strict_types=1);

namespace Querify\Application\Command\EditEligibleApprovers;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
readonly class EditEligibleApprovers
{
    public function __construct(
        public string $externalServiceName,
        /**
         * @var UuidInterface[]
         */
        public array $desiredUserUuidsState
    ) {}
}
