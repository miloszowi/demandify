<?php

declare(strict_types=1);

namespace spec\Querify\Application\Event\DemandSubmitted;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Querify\Application\Command\SendDemandNotification\SendDemandNotification;
use Querify\Domain\Demand\Demand;
use Querify\Domain\Demand\Event\DemandSubmitted;
use Querify\Domain\ExternalService\ExternalServiceConfiguration;
use Querify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Querify\Domain\User\User;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class DemandSubmittedHandlerSpec extends ObjectBehavior
{
    public function let(
        ExternalServiceConfigurationRepository $externalServiceConfigurationRepository,
        MessageBusInterface $messageBus
    ): void {
        $this->beConstructedWith($externalServiceConfigurationRepository, $messageBus);
    }

    public function it_dispatches_send_notification_commands_when_eligible_approvers_exist(
        ExternalServiceConfigurationRepository $externalServiceConfigurationRepository,
        MessageBusInterface $messageBus,
        User $user,
    ): void {
        $demand = new Demand(
            $user->getWrappedObject(),
            'service',
            'content',
            'reason'
        );
        $event = new DemandSubmitted($demand);
        $externalServiceConfiguration = new ExternalServiceConfiguration(
            'service',
            [Uuid::uuid4(), Uuid::uuid4()]
        );
        $externalServiceConfigurationRepository->findByName($event->demand->service)
            ->willReturn($externalServiceConfiguration)
        ;

        $mockEnvelope = new Envelope(new \stdClass());
        $messageBus->dispatch(Argument::type(SendDemandNotification::class))->shouldBeCalledTimes(2)->willReturn($mockEnvelope);

        $this->__invoke($event);
    }

    public function it_does_not_dispatch_send_notification_if_no_eligible_approvers(
        ExternalServiceConfigurationRepository $externalServiceConfigurationRepository,
        MessageBusInterface $messageBus,
        User $user
    ): void {
        $demand = new Demand(
            $user->getWrappedObject(),
            'service',
            'content',
            'reason'
        );
        $event = new DemandSubmitted($demand);
        $externalServiceConfiguration = new ExternalServiceConfiguration(
            'service',
            []
        );
        $externalServiceConfigurationRepository->findByName($event->demand->service)
            ->willReturn($externalServiceConfiguration)
        ;

        $messageBus->dispatch(Argument::type(SendDemandNotification::class))->shouldNotBeCalled();

        $this->__invoke($event);
    }

    public function it_does_not_dispatch_send_notification_if_configuration_not_found(
        ExternalServiceConfigurationRepository $externalServiceConfigurationRepository,
        MessageBusInterface $messageBus,
        User $user
    ): void {
        $demand = new Demand(
            $user->getWrappedObject(),
            'service',
            'content',
            'reason'
        );
        $event = new DemandSubmitted($demand);
        $externalServiceConfigurationRepository->findByName($demand->service)
            ->willReturn(null)
        ;

        $messageBus->dispatch(Argument::type(SendDemandNotification::class))->shouldNotBeCalled();

        $this->__invoke($event);
    }
}
