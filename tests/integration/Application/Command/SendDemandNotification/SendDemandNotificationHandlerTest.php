<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Application\Command\SendDemandNotification;

use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Command\SendDemandNotification\SendDemandNotificationHandler;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Status;
use Demandify\Domain\Notification\NotificationRepository;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\UserRepository;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\External\Slack\Http\Exception\SlackApiException;
use Demandify\Tests\Fixtures\DemandFixture;
use Demandify\Tests\Fixtures\UserFixture;
use Demandify\Tests\Integration\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 */
#[CoversClass(SendDemandNotificationHandler::class)]
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
        self::assertStringContainsString('', $notification->content);
        self::assertSame($notification->attachments[4]['block_id'], $demand->uuid->toString());
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
