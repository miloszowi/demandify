<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Task\Adapter;

use Querify\Domain\Demand\Demand;
use Querify\Domain\ExternalService\ExternalService;
use Querify\Domain\Task\Task;
use Querify\Infrastructure\Task\Adapter;

class Postgres implements Adapter
{
    public function execute(Demand $demand, ExternalService $externalService): Task
    {
        return new Task(
            $demand,
            true,
            1,
            '/dev/null'
        );
    }
}
