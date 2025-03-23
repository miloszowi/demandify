<?php

declare(strict_types=1);

namespace Demandify\Application\Event\TaskSucceeded;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Event\DomainEventHandler;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\Task\Event\TaskSucceeded;

class TaskSucceededHandler implements DomainEventHandler
{
    public function __construct(private readonly CommandBus $commandBus) {}

    public function __invoke(TaskSucceeded $event): void
    {
        $this->commandBus->dispatch(
            new SendDemandNotification(
                $event->task->demand->requester->uuid,
                $event->task->demand,
                NotificationType::TASK_SUCCEEDED
            )
        );
    }
}
