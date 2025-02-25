<?php

namespace Demandify\Application\Event\TaskSucceeded;

use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\Task\Event\TaskSucceeded;
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
