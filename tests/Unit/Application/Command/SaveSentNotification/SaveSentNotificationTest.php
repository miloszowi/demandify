<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\SaveSentNotification;

use Demandify\Application\Command\SaveSentNotification\SaveSentNotification;
use Demandify\Domain\Notification\Notification;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @internal
 */
#[CoversClass(SaveSentNotification::class)]
final class SaveSentNotificationTest extends TestCase
{
    private SaveSentNotification $command;
    private UuidInterface $demandUuid;
    private NotificationType $notificationType;
    private string $notificationIdentifier;
    private string $recipient;

    /** @var mixed[] */
    private array $options;
    private UserSocialAccountType $socialAccountType;

    protected function setUp(): void
    {
        $this->demandUuid = Uuid::fromString('12345678-1234-1234-1234-123456789012');
        $this->notificationType = NotificationType::NEW_DEMAND;
        $this->notificationIdentifier = 'test';
        $this->recipient = 'recipient';
        $this->options = [
            'some_option' => 'some_value',
        ];
        $this->socialAccountType = UserSocialAccountType::SLACK;

        $this->command = new SaveSentNotification(
            $this->demandUuid,
            $this->notificationType,
            $this->notificationIdentifier,
            $this->recipient,
            $this->options,
            $this->socialAccountType
        );
    }

    public function testIsInitializable(): void
    {
        self::assertInstanceOf(SaveSentNotification::class, $this->command);
        self::assertSame($this->demandUuid, $this->command->demandUuid);
        self::assertSame($this->notificationType, $this->command->notificationType);
        self::assertSame($this->notificationIdentifier, $this->command->notificationIdentifier);
        self::assertSame($this->recipient, $this->command->recipient);
        self::assertSame($this->options, $this->command->options);
        self::assertSame($this->socialAccountType, $this->command->socialAccountType);
    }

    public function testItCanTransformToNotification(): void
    {
        $notification = $this->command->toNotification();

        self::assertInstanceOf(Notification::class, $notification);

        self::assertSame($this->demandUuid, $notification->demandUuid);
        self::assertSame($this->notificationType, $notification->type);
        self::assertSame($this->notificationIdentifier, $notification->notificationIdentifier);
        self::assertSame($this->recipient, $notification->recipient);
        self::assertSame($this->options, $notification->options);
        self::assertSame($this->socialAccountType, $notification->socialAccountType);
    }
}
