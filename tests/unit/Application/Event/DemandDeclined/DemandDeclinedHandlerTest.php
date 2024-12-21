<?php

declare(strict_types=1);

namespace Querify\Tests\Unit\Application\Event\DemandDeclined;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Querify\Application\Command\SendDemandNotification\SendDemandNotification;
use Querify\Application\Event\DemandDeclined\DemandDeclinedHandler;
use Querify\Domain\Demand\Demand;
use Querify\Domain\Demand\Event\DemandDeclined;
use Querify\Domain\Notification\Notification;
use Querify\Domain\Notification\NotificationRepository;
use Querify\Domain\Notification\NotificationType;
use Querify\Domain\User\Email;
use Querify\Domain\User\User;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
#[CoversClass(DemandDeclinedHandler::class)]
final class DemandDeclinedHandlerTest extends TestCase
{
    private MessageBusInterface|MockObject $messageBusMock;
    private MockObject|NotificationRepository $notificationRepositoryMock;
    private DemandDeclinedHandler $demandDeclinedHandler;

    protected function setUp(): void
    {
        $this->messageBusMock = $this->createMock(MessageBusInterface::class);
        $this->notificationRepositoryMock = $this->createMock(NotificationRepository::class);
        $this->demandDeclinedHandler = new DemandDeclinedHandler($this->messageBusMock, $this->notificationRepositoryMock);
    }

    public function testDispatchesCommandsWhenDemandIsDeclined(): void
    {
        $notificationMock = $this->createMock(Notification::class);
        $user = new User(Email::fromString('example@local.host'), 'username');
        $demand = new Demand($user, 'some_service', 'content', 'reason');
        $event = new DemandDeclined($demand);

        $this->notificationRepositoryMock
            ->expects(self::once())
            ->method('findByDemandUuidAndAction')->with($event->demand->uuid, NotificationType::NEW_DEMAND)
            ->willReturn([$notificationMock])
        ;
        $mockEnvelope = new Envelope(new \stdClass());
        $this->messageBusMock
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->withAnyParameters()
            ->willReturnOnConsecutiveCalls($mockEnvelope, $mockEnvelope)
        ;

        $this->demandDeclinedHandler->__invoke($event);
    }

    public function testDoesNotDispatchUpdateCommandWhenNoNotificationsWereSent(): void
    {
        $user = new User(Email::fromString('example@local.host'), 'username');
        $demand = new Demand($user, 'some_service', 'content', 'reason');
        $event = new DemandDeclined($demand);

        $this->notificationRepositoryMock
            ->expects(self::once())
            ->method('findByDemandUuidAndAction')
            ->with($event->demand->uuid, NotificationType::NEW_DEMAND)
            ->willReturn([])
        ;
        $mockEnvelope = new Envelope(new \stdClass());
        $this->messageBusMock
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(SendDemandNotification::class))
            ->willReturn($mockEnvelope)
        ;

        $this->demandDeclinedHandler->__invoke($event);
    }
}
