<?php

declare(strict_types=1);

namespace Querify\Tests\Integration\Application\Command\ApproveDemand;

use Querify\Application\Command\ApproveDemand\ApproveDemand;
use Querify\Application\Command\ApproveDemand\ApproveDemandHandler;
use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\Demand\Exception\InvalidDemandStatusException;
use Querify\Domain\Demand\Exception\UserNotAuthorizedToUpdateDemandException;
use Querify\Domain\Demand\Status;
use Querify\Domain\User\Email;
use Querify\Domain\User\UserRepository;
use Querify\Tests\Fixtures\DemandFixture;
use Querify\Tests\Fixtures\ExternalServiceConfigurationFixture;
use Querify\Tests\Fixtures\UserFixture;
use Querify\Tests\integration\BaseKernelTestCase;

/**
 * @internal
 *
 * @covers \Querify\Application\Command\ApproveDemand\ApproveDemandHandler
 */
final class ApproveDemandHandlerTest extends BaseKernelTestCase
{
    private ApproveDemandHandler $approveDemandHandler;
    private DemandRepository $demandRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->load([new DemandFixture(), new ExternalServiceConfigurationFixture()]);

        $this->approveDemandHandler = self::getContainer()->get(ApproveDemandHandler::class);
        $this->demandRepository = self::getContainer()->get(DemandRepository::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function testApprovingDemandIsSuccessful(): void
    {
        $approver = $this->userRepository->getByEmail(Email::fromString(UserFixture::USER_EMAIL_FIXTURE));
        $demand = $this->demandRepository->findInStatus(Status::NEW)[0];

        $command = new ApproveDemand(
            $demand->uuid,
            $approver
        );

        $this->approveDemandHandler->__invoke($command);

        $updatedDemand = $this->demandRepository->getByUuid($demand->uuid);
        self::assertSame(Status::APPROVED, $updatedDemand->status);
        self::assertSame($approver, $updatedDemand->approver);
        self::assertGreaterThan($updatedDemand->createdAt, $updatedDemand->updatedAt);

        self::assertCount(1, $this->getAsyncTransport()->getSent());
    }

    public function testApprovingDemandIsFailingDueToInvalidDemandStatus(): void
    {
        $requester = $this->userRepository->getByEmail(Email::fromString(UserFixture::USER_EMAIL_FIXTURE));
        $demands = $this->demandRepository->findInStatus(Status::APPROVED);

        foreach ($demands as $demand) {
            self::expectException(InvalidDemandStatusException::class);

            $command = new ApproveDemand(
                $demand->uuid,
                $requester
            );
            $this->approveDemandHandler->__invoke($command);

            self::assertCount(0, $this->getAsyncTransport()->getSent());
        }
    }

    public function testItThrowsExceptionDueToNotBeingEligible(): void
    {
        $notEligibleApprover = $this->userRepository->getByEmail(Email::fromString(UserFixture::USER_NOT_ELIGIBLE_TO_APPROVE));
        $requester = $this->userRepository->getByEmail(Email::fromString(UserFixture::USER_EMAIL_FIXTURE));
        $demand = $this->demandRepository->findAllFromUser($requester)[0];

        $command = new ApproveDemand(
            $demand->uuid,
            $notEligibleApprover
        );

        self::expectException(UserNotAuthorizedToUpdateDemandException::class);
        self::expectExceptionMessage(
            \sprintf('User "%s" is not privileged to accept/decline demand for service "%s".', $notEligibleApprover->email, $demand->service)
        );
        $this->approveDemandHandler->__invoke($command);

        self::assertCount(0, $this->getAsyncTransport()->getSent());
    }
}
