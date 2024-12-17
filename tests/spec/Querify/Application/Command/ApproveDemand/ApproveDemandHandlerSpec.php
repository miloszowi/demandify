<?php

declare(strict_types=1);

namespace spec\Querify\Application\Command\ApproveDemand;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Querify\Application\Command\ApproveDemand\ApproveDemand;
use Querify\Application\Command\ApproveDemand\ApproveDemandHandler;
use Querify\Domain\Demand\Demand;
use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\Demand\Exception\DemandNotFoundException;
use Querify\Domain\DomainEvent;
use Querify\Domain\DomainEventPublisher;
use Querify\Domain\ExternalService\ExternalServiceConfiguration;
use Querify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Querify\Domain\User\User;
use Ramsey\Uuid\Uuid;

class ApproveDemandHandlerSpec extends ObjectBehavior
{
    public function let(
        DemandRepository $demandRepository,
        DomainEventPublisher $domainEventPublisher,
        ExternalServiceConfigurationRepository $externalServiceConfigurationRepository
    ): void {
        $this->beConstructedWith($demandRepository, $domainEventPublisher, $externalServiceConfigurationRepository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ApproveDemandHandler::class);
    }

    public function it_approves_demand_and_publishes_event(
        User $user,
        DemandRepository $demandRepository,
        DomainEventPublisher $domainEventPublisher,
        ExternalServiceConfigurationRepository $externalServiceConfigurationRepository,
        ExternalServiceConfiguration $externalServiceConfiguration,
    ): void {
        $demand = new Demand(
            $user->getWrappedObject(),
            'test',
            'test',
            'test'
        );
        $command = new ApproveDemand(
            $demand->uuid,
            $user->getWrappedObject(),
        );

        $externalServiceConfiguration->isUserEligible(Argument::type(User::class))->willReturn(true);
        $externalServiceConfigurationRepository->getByName($demand->service)->willReturn($externalServiceConfiguration);

        $demandRepository->getByUuid($demand->uuid)->willReturn($demand);
        $demandRepository->save($demand)->shouldBeCalled();

        $domainEventPublisher->publish(Argument::type(DomainEvent::class))->shouldBeCalled();

        $this->__invoke($command);
    }

    public function it_throws_an_exception_if_demand_not_found(
        DemandRepository $demandRepository,
        User $user,
    ): void {
        $nonExistingUuid = Uuid::uuid4();
        $command = new ApproveDemand(
            $nonExistingUuid,
            $user->getWrappedObject(),
        );

        $demandRepository->getByUuid($nonExistingUuid)->willThrow(DemandNotFoundException::class);

        $this->shouldThrow(DemandNotFoundException::class)->during(
            '__invoke',
            [$command]
        );
    }

    public function it_throws_an_exception_if_user_is_not_eligible(
        DemandRepository $demandRepository,
        User $user,
        ExternalServiceConfiguration $externalServiceConfiguration,
        ExternalServiceConfigurationRepository $externalServiceConfigurationRepository,
        DomainEventPublisher $domainEventPublisher,
    ): void {
        // todo
        return;
        $demand = new Demand(
            $user->getWrappedObject(),
            'test',
            'test',
            'test'
        );
        $command = new ApproveDemand(
            $demand->uuid,
            $user->getWrappedObject(),
        );

        $externalServiceConfiguration->isUserEligible(Argument::type(User::class))->willReturn(false);
        $externalServiceConfigurationRepository->getByName($demand->service)->willReturn($externalServiceConfiguration);

        $demandRepository->getByUuid($demand->uuid)->willReturn($demand);
        $demandRepository->save($demand)->shouldBeCalled();

        $domainEventPublisher->publish(Argument::type(DomainEvent::class))->shouldNotBeCalled();

        $this->__invoke($command);
    }
}
