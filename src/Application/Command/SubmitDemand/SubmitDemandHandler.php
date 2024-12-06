<?php

declare(strict_types=1);

namespace Querify\Application\Command\SubmitDemand;

use Querify\Domain\Demand\Demand;
use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\Demand\Event\DemandSubmitted;
use Querify\Domain\DomainEventPublisher;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SubmitDemandHandler
{
    public function __construct(
        private readonly DemandRepository $demandRepository,
        private readonly DomainEventPublisher $domainEventPublisher,
    ) {}

    public function __invoke(SubmitDemand $command): void
    {
        $demand = new Demand(
            Uuid::fromString($command->requesterUuid),
            $command->service,
            $command->content,
            $command->reason
        );

        $this->demandRepository->save($demand);
        $this->domainEventPublisher->publish(
            new DemandSubmitted($demand->uuid)
        );
    }
}
