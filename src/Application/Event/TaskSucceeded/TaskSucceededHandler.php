<?php

declare(strict_types=1);

namespace Demandify\Application\Event\TaskSucceeded;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Event\DomainEventHandler;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\Task\Event\TaskSucceeded;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TaskSucceededHandler implements DomainEventHandler
{
    public function __construct(private readonly CommandBus $commandBus) {}

    public function __invoke(TaskSucceeded $event): void
    {
        $this->commandBus->dispatch(
            new SendDemandNotification(
                $event->demand->requester->uuid,
                $event->demand,
                NotificationType::TASK_SUCCEEDED
            )
        );
    }
}
