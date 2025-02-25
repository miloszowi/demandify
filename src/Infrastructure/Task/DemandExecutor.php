<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Task;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\ExternalService\ExternalServiceRepository;
use Demandify\Domain\Task\DemandExecutor as DemandExecutorInterface;
use Demandify\Domain\Task\Task;

class DemandExecutor implements DemandExecutorInterface
{
    public function __construct(
        private readonly ExternalServiceRepository $externalServiceRepository,
        private readonly AdapterFactory $adapterFactory,
    ) {}

    public function execute(Demand $demand): Task
    {
        $externalService = $this->externalServiceRepository->getByName($demand->service);
        $adapter = $this->adapterFactory->create($externalService->type);

        return $adapter->execute($demand, $externalService);
    }
}
