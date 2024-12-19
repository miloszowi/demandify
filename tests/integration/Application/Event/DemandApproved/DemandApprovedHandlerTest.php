<?php

declare(strict_types=1);

namespace Querify\Tests\Integration\Application\Event\DemandApproved;

use PHPUnit\Framework\Attributes\CoversClass;
use Querify\Application\Command\ExecuteDemand\ExecuteDemand;
use Querify\Application\Command\SendDemandNotification\SendDemandNotification;
use Querify\Application\Command\UpdateSentNotificationsWithDecision\UpdateSentNotificationsWithDecision;
use Querify\Application\Event\DemandApproved\DemandApprovedHandler;
use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\Demand\Event\DemandApproved;
use Querify\Domain\Demand\Status;
use Querify\Domain\Notification\NotificationRepository;
use Querify\Domain\Notification\NotificationType;
use Querify\Tests\Fixtures\NotificationFixture;
use Querify\Tests\Integration\BaseKernelTestCase;

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
        $event = new DemandApproved($demand);

        $this->handler->__invoke($event);

        self::assertCount(3, $this->getAsyncTransport()->getSent());
        $sentMessages = $this->getAsyncTransport()->getSent();
        $updateNotifications = $sentMessages[0]->getMessage();
        $executeDemand = $sentMessages[1]->getMessage();
        $sendNotification = $sentMessages[2]->getMessage();

        self::assertInstanceOf(UpdateSentNotificationsWithDecision::class, $updateNotifications);
        self::assertSame($demand, $updateNotifications->demand);

        self::assertInstanceOf(ExecuteDemand::class, $executeDemand);
        self::assertSame($demand, $executeDemand->demand);

        self::assertInstanceOf(SendDemandNotification::class, $sendNotification);
        self::assertSame($demand->requester->uuid, $sendNotification->recipientUuid);
        self::assertSame($demand, $sendNotification->demand);
        self::assertSame(NotificationType::DEMAND_APPROVED, $sendNotification->notificationType);
    }

    public function testItDoesNotDispatchUpdateSentNotificationsWithDecisionWhenThereAreNoNotifications(): void
    {
        $demand = $this->demandRepository->findInStatus(Status::APPROVED)[0];
        $event = new DemandApproved($demand);

        $this->entityManager->remove($this->notificationRepository->findByNotificationIdentifier(NotificationFixture::NOTIFICATION_IDENTIFIER));
        $this->entityManager->flush();
        $this->handler->__invoke($event);

        self::assertCount(2, $this->getAsyncTransport()->getSent());
    }
}
