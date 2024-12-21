<?php

declare(strict_types=1);

namespace Querify\Tests\Unit\Application\Command\UpdateSentNotificationsWithDecision;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Querify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use Querify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecisionHandler;
use Querify\Domain\Demand\Demand;
use Querify\Domain\Notification\Notification;
use Querify\Domain\Notification\NotificationService;

/**
 * @internal
 */
#[CoversClass(UpdateSentNotificationsWithDecisionHandler::class)]
final class UpdateSentNotificationsWithDecisionHandlerTest extends TestCase
{
    private MockObject|NotificationService $notificationServiceMock;
    private UpdateSentNotificationsWithDecisionHandler $updateSentNotificationsWithDecisionHandler;

    protected function setUp(): void
    {
        $this->notificationServiceMock = $this->createMock(NotificationService::class);
        $this->updateSentNotificationsWithDecisionHandler = new UpdateSentNotificationsWithDecisionHandler($this->notificationServiceMock);
    }

    public function testUpdatesSentNotificationsWithDecision(): void
    {
        $notificationMock = $this->createMock(Notification::class);
        $demandMock = $this->createMock(Demand::class);
        $command = new UpdateSentNotificationsWithDecision([$notificationMock], $demandMock);

        $this->notificationServiceMock
            ->expects(self::once())
            ->method('update')
            ->with($notificationMock, $command->demand)
        ;

        $this->updateSentNotificationsWithDecisionHandler->__invoke($command);
    }
}
