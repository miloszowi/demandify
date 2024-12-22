<?php

declare(strict_types=1);

namespace Querify\Application\Event\TaskFailed;

use Querify\Application\Command\SendDemandNotification\SendDemandNotification;
use Querify\Domain\Notification\NotificationType;
use Querify\Domain\Task\Event\TaskFailed;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class TaskFailedHandler
{
    public function __construct(private readonly MessageBusInterface $messageBus) {}

    public function __invoke(TaskFailed $event): void
    {
        $this->messageBus->dispatch(
            new SendDemandNotification(
                $event->task->demand->requester->uuid,
                $event->task->demand,
                NotificationType::TASK_FAILED
            )
        );
    }
}
