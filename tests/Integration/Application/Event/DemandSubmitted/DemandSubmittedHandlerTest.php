<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Application\Event\DemandSubmitted;

use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Event\DemandSubmitted\DemandSubmittedHandler;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Event\DemandSubmitted;
use Demandify\Domain\Demand\Status;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\UserRepository;
use Demandify\Tests\Fixtures\DemandFixture;
use Demandify\Tests\Fixtures\ExternalServiceConfiguration\ExternalServiceConfigurationWithoutEligibleApproversFixture;
use Demandify\Tests\Fixtures\ExternalServiceConfigurationFixture;
use Demandify\Tests\Fixtures\UserFixture;
use Demandify\Tests\Integration\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DemandSubmittedHandler::class)]
final class DemandSubmittedHandlerTest extends BaseKernelTestCase
{
    private DemandSubmittedHandler $handler;
    private DemandRepository $demandRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = self::getContainer()->get(DemandSubmittedHandler::class);
        $this->demandRepository = self::getContainer()->get(DemandRepository::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function testItInformsEligibleApprovers(): void
    {
        $this->load([new DemandFixture(), new ExternalServiceConfigurationFixture()]);
        $demand = $this->demandRepository->findInStatus(Status::NEW)[0];
        $recipient = $this->userRepository->getByEmail(Email::fromString(UserFixture::USER_EMAIL_FIXTURE));
        $event = new DemandSubmitted($demand->uuid);

        $this->handler->__invoke($event);

        self::assertCount(1, $this->getAsyncTransport()->getSent());
        $sentMessages = $this->getAsyncTransport()->getSent();
        $sendNotification = $sentMessages[0]->getMessage();
        self::assertInstanceOf(SendDemandNotification::class, $sendNotification);
        self::assertSame($demand->uuid, $sendNotification->demandUuid);
        self::assertTrue($recipient->uuid->equals($sendNotification->recipientUuid));
    }

    public function testItDoesNothingIfThereAreNoApprovers(): void
    {
        $this->load([new DemandFixture(), new ExternalServiceConfigurationWithoutEligibleApproversFixture()]);
        $demand = $this->demandRepository->findInStatus(Status::NEW)[0];
        $event = new DemandSubmitted($demand->uuid);
        $this->handler->__invoke($event);

        self::assertCount(0, $this->getAsyncTransport()->getSent());
    }
}
