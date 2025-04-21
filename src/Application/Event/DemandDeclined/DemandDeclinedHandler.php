<?php

declare(strict_types=1);

namespace Demandify\Application\Event\DemandDeclined;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use Demandify\Application\Event\DomainEventHandler;
use Demandify\Domain\Demand\Event\DemandDeclined;
use Demandify\Domain\Notification\NotificationType;

class DemandDeclinedHandler implements DomainEventHandler
{
    public function __construct(private readonly CommandBus $commandBus) {}

    public function __invoke(DemandDeclined $event): void
    {
        $this->commandBus->dispatch(
            new UpdateSentNotificationsWithDecision(
                $event->demand,
            )
        );
        $this->commandBus->dispatch(
            new SendDemandNotification(
                $event->demand->requester->uuid,
                $event->demand,
                NotificationType::DEMAND_DECLINED
            )
        );
    }
}
