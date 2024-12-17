<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Task;

use Querify\Domain\ExternalService\ExternalService;

class AdapterFactory
{
    public function create(ExternalService $externalService): Adapter
    {
        exit;
    }
}
