<?php

declare(strict_types=1);

namespace Demandify\Application\Command\ExecuteDemand;

use Demandify\Application\Command\CommandHandler;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\DomainEventPublisher;
use Demandify\Domain\Task\DemandExecutor;
use Demandify\Domain\Task\Event\TaskFailed;
use Demandify\Domain\Task\Event\TaskSucceeded;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ExecuteDemandHandler implements CommandHandler
{
    public function __construct(
        private readonly DemandExecutor $demandExecutor,
        private readonly DemandRepository $demandRepository,
        private readonly DomainEventPublisher $domainEventPublisher,
    ) {}

    public function __invoke(ExecuteDemand $command): void
    {
        $demand = $this->demandRepository->getByUuid($command->demandUuid);

        $demand->start();
        $this->demandRepository->save($demand);

        $task = $this->demandExecutor->execute($demand);

        $demand->finish($task);
        $this->demandRepository->save($demand);

        match ($task->success) {
            true => $this->domainEventPublisher->publish(new TaskSucceeded($task)),
            false => $this->domainEventPublisher->publish(new TaskFailed($task)),
        };
    }
}
