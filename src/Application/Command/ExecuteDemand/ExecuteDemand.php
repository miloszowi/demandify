<?php

declare(strict_types=1);

namespace Querify\Application\Command\ExecuteDemand;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
readonly class ExecuteDemand
{
    public function __construct(public UuidInterface $demandUuid) {}
}
