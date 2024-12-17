<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Task;

use Querify\Domain\Demand\Demand;
use Querify\Domain\ExternalService\ExternalService;
use Querify\Domain\Task\Task;

interface Adapter
{
    public function execute(Demand $demand, ExternalService $externalService): Task;
}
