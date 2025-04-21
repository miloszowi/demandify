<?php

declare(strict_types=1);

namespace Demandify\Application\Event\DemandApproved;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Command\ExecuteDemand\ExecuteDemand;
use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use Demandify\Application\Event\DomainEventHandler;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Event\DemandApproved;
use Demandify\Domain\Notification\NotificationType;

class DemandApprovedHandler implements DomainEventHandler
{
    public function __construct(
        private readonly DemandRepository $demandRepository,
        private readonly CommandBus $commandBus,
    ) {}

    public function __invoke(DemandApproved $event): void
    {
        $demand = $this->demandRepository->getByUuid($event->demandUuid);

        // todo: there is no guarantee all of those 3 messages will be dispatched
        $this->commandBus->dispatch(new UpdateSentNotificationsWithDecision($event->demandUuid));
        $this->commandBus->dispatch(new ExecuteDemand($event->demandUuid));
        $this->commandBus->dispatch(
            new SendDemandNotification(
                $demand->requester->uuid,
                $event->demandUuid,
                NotificationType::DEMAND_APPROVED
            )
        );
    }
}
