<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\UpdateSentNotificationsWithDecision;

use Demandify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use Demandify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecisionHandler;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Notification\Notification;
use Demandify\Domain\Notification\NotificationRepository;
use Demandify\Domain\Notification\NotificationService;
use Demandify\Domain\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UpdateSentNotificationsWithDecisionHandler::class)]
final class UpdateSentNotificationsWithDecisionHandlerTest extends TestCase
{
    private MockObject|NotificationService $notificationServiceMock;
    private DemandRepository|MockObject $demandRepositoryMock;
    private MockObject|NotificationRepository $notificationRepositoryMock;
    private UpdateSentNotificationsWithDecisionHandler $updateSentNotificationsWithDecisionHandler;

    protected function setUp(): void
    {
        $this->notificationServiceMock = $this->createMock(NotificationService::class);
        $this->demandRepositoryMock = $this->createMock(DemandRepository::class);
        $this->notificationRepositoryMock = $this->createMock(NotificationRepository::class);
        $this->updateSentNotificationsWithDecisionHandler = new UpdateSentNotificationsWithDecisionHandler(
            $this->notificationServiceMock,
            $this->demandRepositoryMock,
            $this->notificationRepositoryMock,
        );
    }

    public function testUpdatesSentNotificationsWithDecision(): void
    {
        $notificationMock = $this->createMock(Notification::class);
        $demandMock = new Demand(
            $this->createMock(User::class),
            'some_service',
            'some_content',
            'some_reason',
        );
        $command = new UpdateSentNotificationsWithDecision($demandMock->uuid);

        $this->notificationRepositoryMock
            ->expects(self::once())
            ->method('findByDemandAndType')
            ->willReturn([$notificationMock])
        ;

        $this->demandRepositoryMock
            ->expects(self::once())
            ->method('getByUuid')
            ->with($demandMock->uuid)
            ->willReturn($demandMock)
        ;

        $this->notificationServiceMock
            ->expects(self::once())
            ->method('updateWithDecision')
            ->with($notificationMock, $demandMock)
        ;

        $this->updateSentNotificationsWithDecisionHandler->__invoke($command);
    }

    public function testDoesNotUpdateIfThereAreNoNotifications(): void
    {
        $demandMock = new Demand(
            $this->createMock(User::class),
            'some_service',
            'some_content',
            'some_reason',
        );
        $command = new UpdateSentNotificationsWithDecision($demandMock->uuid);

        $this->notificationRepositoryMock
            ->expects(self::once())
            ->method('findByDemandAndType')
            ->willReturn([])
        ;

        $this->demandRepositoryMock
            ->expects(self::once())
            ->method('getByUuid')
            ->with($demandMock->uuid)
            ->willReturn($demandMock)
        ;

        $this->notificationServiceMock
            ->expects(self::never())
            ->method('updateWithDecision')
            ->withAnyParameters()
        ;

        $this->updateSentNotificationsWithDecisionHandler->__invoke($command);
    }
}
