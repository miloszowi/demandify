<?php

declare(strict_types=1);

namespace Querify\Application\Command\ExecuteDemand;

use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\Task\DemandExecutor;
use Querify\Domain\Task\TaskRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ExecuteDemandHandler
{
    public function __construct(
        private readonly DemandExecutor $demandExecutor,
        private readonly DemandRepository $demandRepository,
        private readonly TaskRepository $taskRepository,
    ) {}

    public function __invoke(ExecuteDemand $command): void
    {
        $command->demand->start();
        $this->demandRepository->save($command->demand);

        $task = $this->demandExecutor->execute($command->demand);

        $command->demand->finish($task);
        $this->demandRepository->save($command->demand);
        $this->taskRepository->save($task);

        // todo TaskSucceeded/TasksFailed event
    }
}
