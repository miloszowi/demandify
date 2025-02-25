<?php

declare(strict_types=1);

namespace Demandify\Domain\Task;

use Demandify\Domain\Demand\Demand;

interface DemandExecutor
{
    public function execute(Demand $demand): Task;
}
