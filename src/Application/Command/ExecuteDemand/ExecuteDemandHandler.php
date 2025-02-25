<?php

declare(strict_types=1);

namespace Demandify\Application\Command\ExecuteDemand;

use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Task\DemandExecutor;
use Demandify\Domain\Task\Event\TaskFailed;
use Demandify\Domain\Task\Event\TaskSucceeded;
use Demandify\Domain\Task\TaskRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class ExecuteDemandHandler
{
    public function __construct(
        private readonly DemandExecutor $demandExecutor,
        private readonly DemandRepository $demandRepository,
        private readonly TaskRepository $taskRepository,
        private readonly MessageBusInterface $messageBus,
    ) {}

    public function __invoke(ExecuteDemand $command): void
    {
        $demand = $this->demandRepository->getByUuid($command->demandUuid);

        $demand->start();
        $this->demandRepository->save($demand);

        $task = $this->demandExecutor->execute($demand);

        $demand->finish($task);
        $this->demandRepository->save($demand);
        $this->taskRepository->save($task);

        match ($task->success) {
            true => $this->messageBus->dispatch(new TaskSucceeded($task)),
            false => $this->messageBus->dispatch(new TaskFailed($task)),
        };
    }
}
