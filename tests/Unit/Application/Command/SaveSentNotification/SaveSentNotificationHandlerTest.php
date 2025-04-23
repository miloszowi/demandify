<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\SaveSentNotification;

use Demandify\Application\Command\SaveSentNotification\SaveSentNotification;
use Demandify\Application\Command\SaveSentNotification\SaveSentNotificationHandler;
use Demandify\Domain\Notification\Notification;
use Demandify\Domain\Notification\NotificationRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SaveSentNotificationHandler::class)]
final class SaveSentNotificationHandlerTest extends TestCase
{
    private SaveSentNotificationHandler $handler;
    private MockObject|NotificationRepository $notificationRepository;

    protected function setUp(): void
    {
        $this->notificationRepository = $this->createMock(NotificationRepository::class);

        $this->handler = new SaveSentNotificationHandler($this->notificationRepository);
    }

    public function testItIsInitializable(): void
    {
        self::assertInstanceOf(SaveSentNotificationHandler::class, $this->handler);
    }

    public function testItSavesNotification(): void
    {
        $command = $this->createMock(SaveSentNotification::class);
        $notificationMock = $this->createMock(Notification::class);

        $command
            ->expects(self::once())
            ->method('toNotification')
            ->willReturn($notificationMock)
        ;

        $this->notificationRepository
            ->expects(self::once())
            ->method('save')
            ->with($notificationMock)
        ;

        $this->handler->__invoke($command);
    }
}
