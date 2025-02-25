<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Infrastructure\Notification;

use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Notification\Notification;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\User\User;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Notification\Client\NotificationClient;
use Demandify\Infrastructure\Notification\Client\Response\SendNotificationResponse;
use Demandify\Infrastructure\Notification\NotificationClientResolver;
use Demandify\Infrastructure\Notification\NotificationService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(NotificationService::class)]
final class NotificationServiceTest extends TestCase
{
    private MockObject|NotificationClientResolver $notificationClientResolver;
    private NotificationService $notificationService;
    private MockObject|NotificationClient $notificationClient;

    protected function setUp(): void
    {
        $this->notificationClientResolver = $this->createMock(NotificationClientResolver::class);
        $this->notificationClient = $this->createMock(NotificationClient::class);
        $this->notificationService = new NotificationService($this->notificationClientResolver);
    }

    public function testSend(): void // todo naming
    {
        $notificationType = NotificationType::NEW_DEMAND;
        $requester = $this->createMock(User::class);
        $demand = new Demand($requester, 'service_name', 'content', 'reason');
        $userSocialAccount = new UserSocialAccount($requester, UserSocialAccountType::SLACK, 'external_id', []);

        $notificationResponse = new SendNotificationResponse(
            'channel',
            'identifier',
            'content',
            [],
        );

        $this->notificationClientResolver
            ->expects(self::once())
            ->method('get')
            ->with(UserSocialAccountType::SLACK)
            ->willReturn($this->notificationClient)
        ;

        $this->notificationClient
            ->expects(self::once())
            ->method('send')
            ->with($notificationType, $demand, $userSocialAccount)
            ->willReturn($notificationResponse)
        ;

        $notification = $this->notificationService->send($notificationType, $demand, $userSocialAccount);

        self::assertInstanceOf(Notification::class, $notification);
        self::assertSame($demand->uuid, $notification->demandUuid);
        self::assertSame($notificationType, $notification->type);
        self::assertSame('identifier', $notification->notificationIdentifier);
        self::assertSame('content', $notification->content);
        self::assertSame([], $notification->attachments);
        self::assertSame('channel', $notification->channel);
        self::assertSame(UserSocialAccountType::SLACK, $notification->socialAccountType);
    }

    public function testUpdate(): void // todo naming
    {
        $demand = $this->createMock(Demand::class);
        $notification = new Notification(
            Uuid::uuid4(),
            NotificationType::NEW_DEMAND,
            'identifier',
            'content',
            [],
            'channel',
            UserSocialAccountType::SLACK
        );

        $this->notificationClientResolver
            ->expects(self::once())
            ->method('get')
            ->with(UserSocialAccountType::SLACK)
            ->willReturn($this->notificationClient)
        ;

        $this->notificationClient
            ->expects(self::once())
            ->method('update')
            ->with($notification, $demand)
        ;

        $this->notificationService->update($notification, $demand);
    }
}
