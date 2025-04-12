<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Event\DemandDeclined;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Event\DemandDeclined\DemandDeclinedHandler;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\Event\DemandDeclined;
use Demandify\Domain\Notification\Notification;
use Demandify\Domain\Notification\NotificationRepository;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DemandDeclinedHandler::class)]
final class DemandDeclinedHandlerTest extends TestCase
{
    private CommandBus|MockObject $commandBusMock;
    private MockObject|NotificationRepository $notificationRepositoryMock;
    private DemandDeclinedHandler $demandDeclinedHandler;

    protected function setUp(): void
    {
        $this->commandBusMock = $this->createMock(CommandBus::class);
        $this->notificationRepositoryMock = $this->createMock(NotificationRepository::class);
        $this->demandDeclinedHandler = new DemandDeclinedHandler($this->commandBusMock, $this->notificationRepositoryMock);
    }

    public function testDispatchesCommandsWhenDemandIsDeclined(): void
    {
        $notificationMock = $this->createMock(Notification::class);
        $user = new User(Email::fromString('example@local.host'));
        $demand = new Demand($user, 'some_service', 'content', 'reason');
        $event = new DemandDeclined($demand);

        $this->notificationRepositoryMock
            ->expects(self::once())
            ->method('findByDemandUuidAndAction')->with($event->demand->uuid, NotificationType::NEW_DEMAND)
            ->willReturn([$notificationMock])
        ;
        $this->commandBusMock
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->withAnyParameters()
        ;

        $this->demandDeclinedHandler->__invoke($event);
    }

    public function testDoesNotDispatchUpdateCommandWhenNoNotificationsWereSent(): void
    {
        $user = new User(Email::fromString('example@local.host'));
        $demand = new Demand($user, 'some_service', 'content', 'reason');
        $event = new DemandDeclined($demand);

        $this->notificationRepositoryMock
            ->expects(self::once())
            ->method('findByDemandUuidAndAction')
            ->with($event->demand->uuid, NotificationType::NEW_DEMAND)
            ->willReturn([])
        ;
        $this->commandBusMock
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(SendDemandNotification::class))
        ;

        $this->demandDeclinedHandler->__invoke($event);
    }
}
