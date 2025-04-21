<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Application\Event\DemandDeclined;

use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use Demandify\Application\Event\DemandDeclined\DemandDeclinedHandler;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Event\DemandDeclined;
use Demandify\Domain\Demand\Status;
use Demandify\Tests\Fixtures\NotificationFixture;
use Demandify\Tests\Integration\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DemandDeclinedHandler::class)]
final class DemandDeclinedHandlerTest extends BaseKernelTestCase
{
    private DemandDeclinedHandler $handler;
    private DemandRepository $demandRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = self::getContainer()->get(DemandDeclinedHandler::class);
        $this->demandRepository = self::getContainer()->get(DemandRepository::class);

        $this->load([new NotificationFixture()]);
    }

    public function testItDeclinesDemandSuccessfully(): void
    {
        $demand = $this->demandRepository->findInStatus(Status::DECLINED)[0];
        $event = new DemandDeclined($demand->uuid);

        $this->handler->__invoke($event);

        self::assertCount(2, $this->getAsyncTransport()->getSent());
        $sentMessages = $this->getAsyncTransport()->getSent();
        $updateNotifications = $sentMessages[0]->getMessage();
        $sendNotification = $sentMessages[1]->getMessage();

        self::assertInstanceOf(UpdateSentNotificationsWithDecision::class, $updateNotifications);
        self::assertSame($demand->uuid, $updateNotifications->demandUuid);

        self::assertInstanceOf(SendDemandNotification::class, $sendNotification);
        self::assertSame($demand->requester->uuid, $sendNotification->recipientUuid);
        self::assertSame($demand->uuid, $sendNotification->demandUuid);
    }
}
