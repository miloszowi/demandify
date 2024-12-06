<?php

declare(strict_types=1);

namespace Querify\Application\Event\DemandCreated;

use Command\NotifyEligibleApprover\NotifyEligibleApprover;
use Querify\Domain\Demand\Event\DemandSubmitted;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DemandSubmittedHandler
{
    public function __construct(private readonly MessageBusInterface $messageBus) {}

    public function __invoke(DemandSubmitted $event): void
    {
        $approversUuids = [Uuid::uuid4()]; // TODO: Provide approver uuids throughout some domain interface

        foreach ($approversUuids as $approverUuid) {
            $this->messageBus->dispatch(
                new NotifyEligibleApprover($approverUuid, $event->demandUuid)
            );
        }
    }
}
