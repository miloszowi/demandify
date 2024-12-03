<?php

declare(strict_types=1);

namespace Querify\Application\Command\SubmitDemand;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SubmitDemandHandler
{
    public function __invoke(SubmitDemand $command): void
    {
        dd($command);
    }
}