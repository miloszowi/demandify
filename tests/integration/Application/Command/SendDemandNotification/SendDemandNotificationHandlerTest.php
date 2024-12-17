<?php

declare(strict_types=1);

namespace Querify\Tests\Integration\Application\Command\SendDemandNotification;

use Querify\Application\Command\SendDemandNotification\SendDemandNotification;
use Querify\Application\Command\SendDemandNotification\SendDemandNotificationHandler;
use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\Demand\Status;
use Querify\Domain\Notification\NotificationRepository;
use Querify\Domain\Notification\NotificationType;
use Querify\Domain\User\Email;
use Querify\Domain\User\UserRepository;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\External\Slack\Http\Exception\SlackApiException;
use Querify\Tests\Fixtures\DemandFixture;
use Querify\Tests\Fixtures\UserFixture;
use Querify\Tests\Integration\BaseKernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 *
 * @covers \Querify\Application\Command\SendDemandNotification\SendDemandNotificationHandler
 */
final class SendDemandNotificationHandlerTest extends BaseKernelTestCase
{
    private SendDemandNotificationHandler $handler;
    private NotificationRepository $notificationRepository;
    private UserRepository $userRepository;
    private DemandRepository $demandRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = self::getContainer()->get(SendDemandNotificationHandler::class);
        $this->notificationRepository = self::getContainer()->get(NotificationRepository::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->demandRepository = self::getContainer()->get(DemandRepository::class);

        $this->load([new UserFixture(), new DemandFixture()]);
    }

    public function testSendingNotificationWillBeSuccessful(): void
    {
        $demand = $this->demandRepository->findInStatus(Status::APPROVED)[0];

        $command = new SendDemandNotification(
            $this->userRepository->getByEmail(Email::fromString(UserFixture::USER_WITH_SLACK_SOCIAL_ACCOUNT))->uuid,
            $demand,
            NotificationType::NEW_DEMAND
        );

        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockResponse
            ->method('getContent')
            ->willReturn(json_encode(
                [
                    'ok' => true,
                    'channel' => 'some channel',
                    'ts' => '12345678.123456',
                ]
            ))
        ;

        $mockHttpClient
            ->method('request')
            ->willReturn($mockResponse)
        ;

        self::getContainer()->set('http_client.slack', $mockHttpClient);

        $this->handler->__invoke($command);

        $notifications = $this->notificationRepository->findByDemandUuidAndAction($demand->uuid, NotificationType::NEW_DEMAND);
        self::assertCount(1, $notifications);
        $notification = $notifications[0];
        self::assertSame('some channel', $notification->channel);
        self::assertSame('12345678.123456', $notification->notificationIdentifier);
        // @see DemandFixture
        self::assertStringContainsString('test approved content', $notification->content);
        self::assertCount(1, $notification->attachments);
        self::assertSame(UserSocialAccountType::SLACK, $notification->socialAccountType);
    }

    public function testSendingNotificationForNonAuthedUserWillThrowException(): void
    {
        $demand = $this->demandRepository->findInStatus(Status::APPROVED)[0];

        $command = new SendDemandNotification(
            $this->userRepository->getByEmail(Email::fromString(UserFixture::USER_WITH_SLACK_SOCIAL_ACCOUNT))->uuid,
            $demand,
            NotificationType::NEW_DEMAND
        );

        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockResponse
            ->method('getContent')
            ->willReturn(json_encode(
                [
                    'ok' => false,
                    'error' => 'not_authed',
                ]
            ))
        ;

        $mockHttpClient
            ->method('request')
            ->willReturn($mockResponse)
        ;

        self::getContainer()->set('http_client.slack', $mockHttpClient);

        self::expectException(SlackApiException::class);
        $this->handler->__invoke($command);
        self::assertCount(
            0,
            $this->notificationRepository->findByDemandUuidAndAction($demand->uuid, NotificationType::NEW_DEMAND)
        );
    }
}
