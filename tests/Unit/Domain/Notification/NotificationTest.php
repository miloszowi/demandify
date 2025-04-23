<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\Notification;

use Demandify\Domain\Notification\Notification;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(Notification::class)]
final class NotificationTest extends TestCase
{
    public function testItStoresProvidedValues(): void
    {
        $demandUuid = Uuid::uuid4();
        $notificationType = NotificationType::NEW_DEMAND;
        $socialAccountType = UserSocialAccountType::SLACK;

        $notification = new Notification(
            $demandUuid,
            $notificationType,
            'recipient@example.com',
            'identifier123',
            ['key' => 'value'],
            $socialAccountType
        );

        self::assertSame($demandUuid, $notification->demandUuid);
        self::assertSame($notificationType, $notification->type);
        self::assertSame('recipient@example.com', $notification->recipient);
        self::assertSame('identifier123', $notification->notificationIdentifier);
        self::assertSame(['key' => 'value'], $notification->options);
        self::assertSame($socialAccountType, $notification->socialAccountType);
        self::assertInstanceOf(\DateTimeImmutable::class, $notification->createdAt);
    }
}
