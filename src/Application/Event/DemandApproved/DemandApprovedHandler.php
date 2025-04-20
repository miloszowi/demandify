<?php

declare(strict_types=1);

namespace Demandify\Application\Event\DemandApproved;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Command\ExecuteDemand\ExecuteDemand;
use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use Demandify\Application\Event\DomainEventHandler;
use Demandify\Domain\Demand\Event\DemandApproved;
use Demandify\Domain\Notification\NotificationType;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DemandApprovedHandler implements DomainEventHandler
{
    public function __construct(private readonly CommandBus $commandBus) {}

    public function __invoke(DemandApproved $event): void
    {
        // todo: there is no guarantee all of those 3 messages will be dispatched
        $this->commandBus->dispatch(new UpdateSentNotificationsWithDecision($event->demand));
        $this->commandBus->dispatch(new ExecuteDemand($event->demand->uuid));
        $this->commandBus->dispatch(
            new SendDemandNotification(
                $event->demand->requester->uuid,
                $event->demand,
                NotificationType::DEMAND_APPROVED
            )
        );
    }
}
