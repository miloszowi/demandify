<?php

declare(strict_types=1);

namespace Querify\Application\Command\ExecuteDemand;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ExecuteDemandHandler
{
    public function __invoke(ExecuteDemand $command): void
    {
        // TODO: Implement __invoke() method.
    }
}
