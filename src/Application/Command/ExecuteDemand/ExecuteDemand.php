<?php

declare(strict_types=1);

namespace Demandify\Application\Command\ExecuteDemand;

use Demandify\Application\Command\Command;
use Ramsey\Uuid\UuidInterface;

readonly class ExecuteDemand implements Command
{
    public function __construct(public UuidInterface $demandUuid) {}
}
