<?php

declare(strict_types=1);

namespace spec\Querify\Application\Command\SubmitDemand;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Querify\Application\Command\SubmitDemand\SubmitDemand;
use Querify\Domain\Demand\Demand;
use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\Demand\Event\DemandSubmitted;
use Querify\Domain\DomainEventPublisher;
use Querify\Domain\User\Email;
use Querify\Domain\User\User;
use Querify\Domain\User\UserRepository;

class SubmitDemandHandlerSpec extends ObjectBehavior
{
    public function let(
        DemandRepository $demandRepository,
        DomainEventPublisher $domainEventPublisher,
        UserRepository $userRepository
    ): void {
        $this->beConstructedWith($demandRepository, $domainEventPublisher, $userRepository);
    }

    public function it_submits_demand(
        DemandRepository $demandRepository,
        DomainEventPublisher $domainEventPublisher,
        UserRepository $userRepository,
        User $user,
        Demand $demand
    ): void {
        $command = new SubmitDemand(
            'example@local.host',
            'example-service',
            'content',
            'reason'
        );

        $userRepository->getByEmail(Email::fromString($command->requesterEmail))->willReturn($user);

        $demandRepository->save(Argument::type(Demand::class))->shouldBeCalled();
        $domainEventPublisher->publish(Argument::type(DemandSubmitted::class))->shouldBeCalled();

        $this->__invoke($command);
    }
}
