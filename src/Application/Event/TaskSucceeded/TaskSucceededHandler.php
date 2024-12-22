<?php

namespace Querify\Application\Event\TaskSucceeded;

use Querify\Application\Command\SendDemandNotification\SendDemandNotification;
use Querify\Domain\Notification\NotificationType;
use Querify\Domain\Task\Event\TaskSucceeded;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class TaskSucceededHandler
{
    public function __construct(private readonly MessageBusInterface $messageBus) {}

    public function __invoke(TaskSucceeded $event): void
    {
        $this->messageBus->dispatch(
            new SendDemandNotification(
                $event->task->demand->requester->uuid,
                $event->task->demand,
                NotificationType::TASK_SUCCEEDED
            )
        );
    }
}
