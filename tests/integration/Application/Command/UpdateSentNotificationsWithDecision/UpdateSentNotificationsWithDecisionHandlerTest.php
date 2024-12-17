<?php

declare(strict_types=1);

namespace Querify\Tests\Integration\Application\Command\UpdateSentNotificationsWithDecision;

use Querify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use Querify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecisionHandler;
use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\Demand\Status;
use Querify\Domain\Notification\NotificationRepository;
use Querify\Tests\Fixtures\NotificationFixture;
use Querify\Tests\Integration\BaseKernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 *
 * @covers \Querify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecisionHandler
 */
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
            ->expects(self::never())
            ->method('getContent')
            ->willReturn('')
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
