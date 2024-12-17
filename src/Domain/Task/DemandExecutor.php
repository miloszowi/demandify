<?php

declare(strict_types=1);

namespace Querify\Domain\Task;

use Querify\Domain\Demand\Demand;

interface DemandExecutor
{
    public function execute(Demand $demand): Task;
}
