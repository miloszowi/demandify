<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Task;

use Querify\Domain\Demand\Demand;
use Querify\Domain\ExternalService\ExternalServiceRepository;
use Querify\Domain\Task\DemandExecutor as DemandExecutorInterface;
use Querify\Domain\Task\Task;

class DemandExecutor implements DemandExecutorInterface
{
    public function __construct(
        private readonly ExternalServiceRepository $externalServiceRepository,
        private readonly AdapterFactory $adapterFactory,
    ) {}

    public function execute(Demand $demand): Task
    {
        $externalService = $this->externalServiceRepository->getByName($demand->service);

        $adpater = $this->adapterFactory->create($externalService);

        return new Task(
            $demand,
            false,
            0,
        );
    }
}
