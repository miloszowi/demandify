<?php

declare(strict_types=1);

namespace Querify\Tests\Integration\Application\Command\DeclineDemand;

use Querify\Application\Command\DeclineDemand\DeclineDemand;
use Querify\Application\Command\DeclineDemand\DeclineDemandHandler;
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
 * @covers \Querify\Application\Command\DeclineDemand\DeclineDemandHandler
 */
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
            $approver
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
                $requester
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
            $notEligibleApprover
        );

        self::expectException(UserNotAuthorizedToUpdateDemandException::class);
        self::expectExceptionMessage(
            \sprintf('User "%s" is not privileged to accept/decline demand for service "%s".', $notEligibleApprover->email, $demand->service)
        );
        $this->declineDemandHandler->__invoke($command);

        self::assertCount(0, $this->getAsyncTransport()->getSent());
    }
}
