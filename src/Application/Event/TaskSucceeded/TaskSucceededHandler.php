<?php

declare(strict_types=1);

namespace Demandify\Application\Event\TaskSucceeded;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Event\DomainEventHandler;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\Task\Event\TaskSucceeded;

class TaskSucceededHandler implements DomainEventHandler
{
    public function __construct(
        private readonly DemandRepository $demandRepository,
        private readonly CommandBus $commandBus
    ) {}

    public function __invoke(TaskSucceeded $event): void
    {
        $demand = $this->demandRepository->getByUuid($event->demandUuid);

        $this->commandBus->dispatch(
            new SendDemandNotification(
                $demand->requester->uuid,
                $event->demandUuid,
                NotificationType::TASK_SUCCEEDED
            )
        );
    }
}
