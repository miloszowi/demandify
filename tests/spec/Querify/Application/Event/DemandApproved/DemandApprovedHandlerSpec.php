<?php

declare(strict_types=1);

namespace spec\Querify\Application\Event\DemandApproved;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Querify\Application\Command\ExecuteDemand\ExecuteDemand;
use Querify\Application\Command\SendDemandNotification\SendDemandNotification;
use Querify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use Querify\Domain\Demand\Demand;
use Querify\Domain\Demand\Event\DemandApproved;
use Querify\Domain\Notification\Notification;
use Querify\Domain\Notification\NotificationRepository;
use Querify\Domain\Notification\NotificationType;
use Querify\Domain\User\Email;
use Querify\Domain\User\User;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class DemandApprovedHandlerSpec extends ObjectBehavior
{
    public function let(
        MessageBusInterface $messageBus,
        NotificationRepository $notificationRepository
    ): void {
        $this->beConstructedWith($messageBus, $notificationRepository);
    }

    public function it_dispatches_commands_when_demand_is_approved(
        MessageBusInterface $messageBus,
        NotificationRepository $notificationRepository,
        Notification $notification
    ): void {
        $user = new User(
            Email::fromString('example@local.host'),
            'username'
        );
        $demand = new Demand(
            $user,
            'some_service',
            'content',
            'reason'
        );

        $event = new DemandApproved(
            $demand
        );
        $notificationRepository->findByDemandUuidAndAction($event->demand->uuid, NotificationType::NEW_DEMAND)
            ->willReturn([$notification])
        ;

        $mockEnvelope = new Envelope(new \stdClass());

        $messageBus->dispatch(Argument::type(ExecuteDemand::class))->shouldBeCalled()->willReturn($mockEnvelope);
        $messageBus->dispatch(Argument::type(UpdateSentNotificationsWithDecision::class))->shouldBeCalled()->willReturn($mockEnvelope);
        $messageBus->dispatch(Argument::type(SendDemandNotification::class))->shouldBeCalled()->willReturn($mockEnvelope);

        $this->__invoke($event);
    }

    public function it_does_not_dispatch_update_command_when_no_notifications_were_sent(
        MessageBusInterface $messageBus,
        NotificationRepository $notificationRepository,
    ): void {
        $user = new User(
            Email::fromString('example@local.host'),
            'username'
        );
        $demand = new Demand(
            $user,
            'some_service',
            'content',
            'reason'
        );

        $event = new DemandApproved(
            $demand
        );
        $notificationRepository->findByDemandUuidAndAction($event->demand->uuid, NotificationType::NEW_DEMAND)
            ->willReturn([])
        ;

        $mockEnvelope = new Envelope(new \stdClass());

        $messageBus->dispatch(Argument::type(ExecuteDemand::class))->shouldBeCalled()->willReturn($mockEnvelope);
        $messageBus->dispatch(Argument::type(UpdateSentNotificationsWithDecision::class))->shouldNotBeCalled();
        $messageBus->dispatch(Argument::type(SendDemandNotification::class))->shouldBeCalled()->willReturn($mockEnvelope);

        $this->__invoke($event);
    }
}
