<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Infrastructure\Notification;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Notification\Client\Exception\NotificationClientNotImplementedException;
use Demandify\Infrastructure\Notification\Client\NotificationClient;
use Demandify\Infrastructure\Notification\NotificationClientResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(NotificationClientResolver::class)]
final class NotificationClientResolverTest extends TestCase
{
    public function testGetReturnsNotificationClient(): void
    {
        $userSocialAccountType = UserSocialAccountType::SLACK;
        $notificationClient = $this->createMock(NotificationClient::class);
        $notificationClient->method('supports')->with($userSocialAccountType)->willReturn(true);

        $resolver = new NotificationClientResolver([$notificationClient]);

        $result = $resolver->get($userSocialAccountType);

        self::assertSame($notificationClient, $result);
    }

    public function testGetThrowsExceptionWhenClientNotFound(): void
    {
        $this->expectException(NotificationClientNotImplementedException::class);

        $userSocialAccountType = UserSocialAccountType::SLACK;
        $notificationClient = $this->createMock(NotificationClient::class);
        $notificationClient->method('supports')->with($userSocialAccountType)->willReturn(false);

        $resolver = new NotificationClientResolver([$notificationClient]);

        $resolver->get($userSocialAccountType);
    }
}
