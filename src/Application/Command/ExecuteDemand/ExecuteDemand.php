<?php

declare(strict_types=1);

namespace Querify\Application\Command\ExecuteDemand;

use Querify\Domain\Demand\Demand;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
readonly class ExecuteDemand
{
    public function __construct(public Demand $demand) {}
}
