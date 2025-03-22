<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Event\DemandApproved;

use Demandify\Application\Event\DemandApproved\DemandApprovedHandler;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\Event\DemandApproved;
use Demandify\Domain\Notification\Notification;
use Demandify\Domain\Notification\NotificationRepository;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
#[CoversClass(DemandApprovedHandler::class)]
final class DemandApprovedHandlerTest extends TestCase
{
    private MessageBusInterface|MockObject $messageBusMock;
    private MockObject|NotificationRepository $notificationRepositoryMock;
    private DemandApprovedHandler $demandApprovedHandler;

    protected function setUp(): void
    {
        $this->messageBusMock = $this->createMock(MessageBusInterface::class);
        $this->notificationRepositoryMock = $this->createMock(NotificationRepository::class);
        $this->demandApprovedHandler = new DemandApprovedHandler($this->messageBusMock, $this->notificationRepositoryMock);
    }

    public function testDispatchesCommandsWhenDemandIsApproved(): void
    {
        $notificationMock = $this->createMock(Notification::class);
        $user = new User(Email::fromString('example@local.host'));
        $demand = new Demand($user, 'some_service', 'content', 'reason');
        $event = new DemandApproved($demand);

        $this->notificationRepositoryMock
            ->expects(self::once())
            ->method('findByDemandUuidAndAction')->with($event->demand->uuid, NotificationType::NEW_DEMAND)
            ->willReturn([$notificationMock])
        ;
        $mockEnvelope = new Envelope(new \stdClass());
        $this->messageBusMock
            ->expects(self::exactly(3))
            ->method('dispatch')
            ->withAnyParameters()
            ->willReturnOnConsecutiveCalls($mockEnvelope, $mockEnvelope, $mockEnvelope)
        ;

        $this->demandApprovedHandler->__invoke($event);
    }

    public function testDoesNotDispatchUpdateCommandWhenNoNotificationsWereSent(): void
    {
        $user = new User(Email::fromString('example@local.host'));
        $demand = new Demand($user, 'some_service', 'content', 'reason');
        $event = new DemandApproved($demand);

        $this->notificationRepositoryMock
            ->expects(self::once())
            ->method('findByDemandUuidAndAction')
            ->with($event->demand->uuid, NotificationType::NEW_DEMAND)
            ->willReturn([])
        ;
        $mockEnvelope = new Envelope(new \stdClass());
        $this->messageBusMock
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->withAnyParameters()
            ->willReturnOnConsecutiveCalls($mockEnvelope, $mockEnvelope)
        ;

        $this->demandApprovedHandler->__invoke($event);
    }
}
