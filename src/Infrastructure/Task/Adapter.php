<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Task;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\ExternalService\ExternalService;
use Demandify\Domain\Task\Task;

interface Adapter
{
    public function execute(Demand $demand, ExternalService $externalService): Task;
}
