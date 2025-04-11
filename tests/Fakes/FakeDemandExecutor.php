<?php

declare(strict_types=1);

namespace Demandify\Tests\Fakes;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Task\DemandExecutor;

class DemandExecutorFake implements DemandExecutor
{
    public function execute(Demand $demand): void
    {
        
    }
}