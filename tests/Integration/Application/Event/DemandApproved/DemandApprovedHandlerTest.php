<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Application\Event\DemandApproved;

use Demandify\Application\Command\ExecuteDemand\ExecuteDemand;
use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use Demandify\Application\Event\DemandApproved\DemandApprovedHandler;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Event\DemandApproved;
use Demandify\Domain\Demand\Status;
use Demandify\Domain\Notification\NotificationRepository;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Tests\Fixtures\NotificationFixture;
use Demandify\Tests\Integration\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DemandApprovedHandler::class)]
final class DemandApprovedHandlerTest extends BaseKernelTestCase
{
    private DemandApprovedHandler $handler;
    private NotificationRepository $notificationRepository;
    private DemandRepository $demandRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = self::getContainer()->get(DemandApprovedHandler::class);
        $this->notificationRepository = self::getContainer()->get(NotificationRepository::class);
        $this->demandRepository = self::getContainer()->get(DemandRepository::class);

        $this->load([new NotificationFixture()]);
    }

    public function testItHandlesDemandApprovedEventSucesfully(): void
    {
        $demand = $this->demandRepository->findInStatus(Status::APPROVED)[0];
        $event = new DemandApproved($demand->uuid);

        $this->handler->__invoke($event);

        self::assertCount(2, $this->getAsyncTransport()->getSent());
        self::assertCount(1, $this->getTransport('task')->getSent());
        $sentMessages = $this->getAsyncTransport()->getSent();
        $updateNotifications = $sentMessages[0]->getMessage();
        $sendNotification = $sentMessages[1]->getMessage();
        $executeDemand = $this->getTransport('task')->getSent()[0]->getMessage();

        self::assertInstanceOf(UpdateSentNotificationsWithDecision::class, $updateNotifications);
        self::assertSame($demand->uuid, $updateNotifications->demandUuid);

        self::assertInstanceOf(ExecuteDemand::class, $executeDemand);
        self::assertSame($demand->uuid, $executeDemand->demandUuid);

        self::assertInstanceOf(SendDemandNotification::class, $sendNotification);
        self::assertSame($demand->requester->uuid, $sendNotification->recipientUuid);
        self::assertSame($demand->uuid, $sendNotification->demandUuid);
        self::assertSame(NotificationType::DEMAND_APPROVED, $sendNotification->notificationType);
    }

    public function testItDoesNotDispatchUpdateSentNotificationsWithDecisionWhenThereAreNoNotifications(): void
    {
        $demand = $this->demandRepository->findInStatus(Status::APPROVED)[0];
        $event = new DemandApproved($demand->uuid);

        $this->getEntityManager()->remove($this->notificationRepository->findByNotificationIdentifier(NotificationFixture::NOTIFICATION_IDENTIFIER));
        $this->getEntityManager()->flush();
        $this->handler->__invoke($event);

        self::assertCount(2, $this->getAsyncTransport()->getSent());
    }
}
