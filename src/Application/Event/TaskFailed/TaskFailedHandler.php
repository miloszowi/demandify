<?php

declare(strict_types=1);

namespace Demandify\Application\Event\TaskFailed;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Event\DomainEventHandler;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\Task\Event\TaskFailed;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TaskFailedHandler implements DomainEventHandler
{
    public function __construct(private readonly CommandBus $commandBus) {}

    public function __invoke(TaskFailed $event): void
    {
        $this->commandBus->dispatch(
            new SendDemandNotification(
                $event->task->demand->requester->uuid,
                $event->task->demand,
                NotificationType::TASK_FAILED
            )
        );
    }
}
