<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\SendDemandNotification;

use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Domain\Notification\NotificationType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(SendDemandNotification::class)]
final class SendDemandNotificationTest extends TestCase
{
    public function testIsInitializable(): void
    {
        $command = new SendDemandNotification(
            Uuid::uuid4(),
            Uuid::uuid4(),
            NotificationType::NEW_DEMAND
        );

        self::assertInstanceOf(SendDemandNotification::class, $command);
    }
}
