<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Application\Command\SendDemandNotification;

use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Command\SendDemandNotification\SendDemandNotificationHandler;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Status;
use Demandify\Domain\Notification\NotificationType;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\UserRepository;
use Demandify\Tests\Fixtures\DemandFixture;
use Demandify\Tests\Fixtures\UserFixture;
use Demandify\Tests\Integration\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(SendDemandNotificationHandler::class)]
final class SendDemandNotificationHandlerTest extends BaseKernelTestCase
{
    private SendDemandNotificationHandler $handler;
    private DemandRepository $demandRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = self::getContainer()->get(SendDemandNotificationHandler::class);
        $this->demandRepository = self::getContainer()->get(DemandRepository::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);

        $this->load([new UserFixture(), new DemandFixture()]);
    }

    public function testItSendsNotificationToRecipient(): void
    {
        $demand = $this->demandRepository->findInStatus(Status::NEW)[0];
        $command = new SendDemandNotification(
            $this->userRepository->getByEmail(Email::fromString(UserFixture::USER_WITH_SLACK_SOCIAL_ACCOUNT))->uuid,
            $demand,
            NotificationType::NEW_DEMAND
        );

        $this->handler->__invoke($command);

        self::assertCount(1, $this->getTransport('notification')->getSent());
    }

    public function testItDoesNotSendNotificationForNotNotifiableUserAccount(): void
    {
        $demand = $this->demandRepository->findInStatus(Status::NEW)[0];
        $command = new SendDemandNotification(
            $this->userRepository->getByEmail(Email::fromString(UserFixture::USER_WITH_NOT_NOTIFIABLE_SOCIAL_ACCOUNT))->uuid,
            $demand,
            NotificationType::NEW_DEMAND
        );

        $this->handler->__invoke($command);

        self::assertCount(0, $this->getTransport('notification')->getSent());
    }
}
