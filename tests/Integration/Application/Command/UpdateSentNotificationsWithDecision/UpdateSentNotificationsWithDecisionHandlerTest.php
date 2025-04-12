<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Application\Command\UpdateSentNotificationsWithDecision;

use Demandify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use Demandify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecisionHandler;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Status;
use Demandify\Domain\Notification\NotificationRepository;
use Demandify\Tests\Fixtures\NotificationFixture;
use Demandify\Tests\Integration\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 */
#[CoversClass(UpdateSentNotificationsWithDecisionHandler::class)]
final class UpdateSentNotificationsWithDecisionHandlerTest extends BaseKernelTestCase
{
    private UpdateSentNotificationsWithDecisionHandler $handler;
    private NotificationRepository $notificationRepository;
    private DemandRepository $demandRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = self::getContainer()->get(UpdateSentNotificationsWithDecisionHandler::class);
        $this->notificationRepository = self::getContainer()->get(NotificationRepository::class);
        $this->demandRepository = self::getContainer()->get(DemandRepository::class);
        $this->load([new NotificationFixture()]);
    }

    public function testUpdatingNotificationsWillBeSuccessful(): void
    {
        $notification = $this->notificationRepository->findByNotificationIdentifier(NotificationFixture::NOTIFICATION_IDENTIFIER);
        $command = new UpdateSentNotificationsWithDecision(
            [$notification],
            $this->demandRepository->findInStatus(Status::APPROVED)[0]
        );

        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockResponse
            ->expects(self::once())
            ->method('toArray')
            ->willReturn(['ok' => true])
        ;

        $mockHttpClient
            ->expects(self::once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        self::getContainer()->set('http_client.slack', $mockHttpClient);

        $this->handler->__invoke($command);
    }
}
