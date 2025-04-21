<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Application\Command\DeclineDemand;

use Demandify\Application\Command\DeclineDemand\DeclineDemand;
use Demandify\Application\Command\DeclineDemand\DeclineDemandHandler;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Exception\InvalidDemandStatusException;
use Demandify\Domain\Demand\Exception\UserNotAuthorizedToUpdateDemandException;
use Demandify\Domain\Demand\Status;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\UserRepository;
use Demandify\Tests\Fixtures\DemandFixture;
use Demandify\Tests\Fixtures\ExternalServiceConfigurationFixture;
use Demandify\Tests\Fixtures\UserFixture;
use Demandify\Tests\Integration\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DeclineDemandHandler::class)]
final class DeclineDemandHandlerTest extends BaseKernelTestCase
{
    private DeclineDemandHandler $declineDemandHandler;
    private DemandRepository $demandRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->load([new DemandFixture(), new ExternalServiceConfigurationFixture()]);

        $this->declineDemandHandler = self::getContainer()->get(DeclineDemandHandler::class);
        $this->demandRepository = self::getContainer()->get(DemandRepository::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function testDecliningDemandIsSuccessful(): void
    {
        $approver = $this->userRepository->getByEmail(Email::fromString(UserFixture::USER_EMAIL_FIXTURE));
        $demand = $this->demandRepository->findInStatus(Status::NEW)[0];

        $command = new DeclineDemand(
            $demand->uuid,
            $approver->uuid
        );

        $this->declineDemandHandler->__invoke($command);

        $updatedDemand = $this->demandRepository->getByUuid($demand->uuid);
        self::assertSame(Status::DECLINED, $updatedDemand->status);
        self::assertSame($approver, $updatedDemand->approver);
        self::assertGreaterThan($updatedDemand->createdAt, $updatedDemand->updatedAt);

        self::assertCount(1, $this->getAsyncTransport()->getSent());
    }

    public function testDecliningDemandIsFailingDueToInvalidDemandStatus(): void
    {
        $requester = $this->userRepository->getByEmail(Email::fromString(UserFixture::USER_EMAIL_FIXTURE));
        $demands = $this->demandRepository->findInStatus(Status::DECLINED);

        foreach ($demands as $demand) {
            self::expectException(InvalidDemandStatusException::class);

            $command = new DeclineDemand(
                $demand->uuid,
                $requester->uuid
            );
            $this->declineDemandHandler->__invoke($command);

            self::assertCount(0, $this->getAsyncTransport()->getSent());
        }
    }

    public function testItThrowsExceptionDueToNotBeingEligible(): void
    {
        $notEligibleApprover = $this->userRepository->getByEmail(Email::fromString(UserFixture::USER_NOT_ELIGIBLE_TO_APPROVE));
        $requester = $this->userRepository->getByEmail(Email::fromString(UserFixture::USER_EMAIL_FIXTURE));
        $demand = $this->demandRepository->findAllFromUser($requester)[0];

        $command = new DeclineDemand(
            $demand->uuid,
            $notEligibleApprover->uuid
        );

        self::expectException(UserNotAuthorizedToUpdateDemandException::class);
        self::expectExceptionMessage(
            \sprintf('User "%s" is not privileged to accept/decline demand for service "%s".', $notEligibleApprover->email, $demand->service)
        );
        $this->declineDemandHandler->__invoke($command);

        self::assertCount(0, $this->getAsyncTransport()->getSent());
    }
}
