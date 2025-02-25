<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Task\Adapter;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\ExternalService\ExternalService;
use Demandify\Domain\Task\Task;
use Demandify\Infrastructure\Task\Adapter;

class Maria implements Adapter
{
    public function execute(Demand $demand, ExternalService $externalService): Task
    {
        throw new \Exception('Not implemented');
    }
}
