<?php

declare(strict_types=1);

namespace Demandify\Application\Command\SubmitDemand;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Event\DemandSubmitted;
use Demandify\Domain\DomainEventPublisher;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SubmitDemandHandler
{
    public function __construct(
        private readonly DemandRepository $demandRepository,
        private readonly DomainEventPublisher $domainEventPublisher,
        private readonly UserRepository $userRepository,
    ) {}

    public function __invoke(SubmitDemand $command): void
    {
        $user = $this->userRepository->getByEmail(Email::fromString($command->requesterEmail));

        $demand = new Demand(
            $user,
            $command->service,
            $command->content,
            $command->reason
        );

        $this->demandRepository->save($demand);
        $this->domainEventPublisher->publish(new DemandSubmitted($demand));
    }
}
