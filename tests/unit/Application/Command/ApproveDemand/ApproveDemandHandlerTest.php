<?php

declare(strict_types=1);

namespace Querify\Tests\Unit\Application\Command\ApproveDemand;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Querify\Application\Command\ApproveDemand\ApproveDemand;
use Querify\Application\Command\ApproveDemand\ApproveDemandHandler;
use Querify\Domain\Demand\Demand;
use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\Demand\Event\DemandApproved;
use Querify\Domain\Demand\Exception\DemandNotFoundException;
use Querify\Domain\Demand\Exception\UserNotAuthorizedToUpdateDemandException;
use Querify\Domain\DomainEventPublisher;
use Querify\Domain\ExternalService\ExternalServiceConfiguration;
use Querify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Querify\Domain\User\Email;
use Querify\Domain\User\User;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(ApproveDemandHandler::class)]
final class ApproveDemandHandlerTest extends TestCase
{
    private DemandRepository|MockObject $demandRepositoryMock;
    private DomainEventPublisher|MockObject $domainEventPublisherMock;
    private ExternalServiceConfigurationRepository|MockObject $externalServiceConfigRepoMock;
    private ApproveDemandHandler $handler;

    protected function setUp(): void
    {
        $this->demandRepositoryMock = $this->createMock(DemandRepository::class);
        $this->domainEventPublisherMock = $this->createMock(DomainEventPublisher::class);
        $this->externalServiceConfigRepoMock = $this->createMock(ExternalServiceConfigurationRepository::class);

        $this->handler = new ApproveDemandHandler(
            $this->demandRepositoryMock,
            $this->domainEventPublisherMock,
            $this->externalServiceConfigRepoMock
        );
    }

    public function testIsInitializable(): void
    {
        self::assertInstanceOf(ApproveDemandHandler::class, $this->handler);
    }

    public function testApprovesDemandAndPublishesDemandApprovedEvent(): void
    {
        $userMock = $this->createMock(User::class);
        $externalServiceConfigMock = $this->createMock(ExternalServiceConfiguration::class);
        $demand = new Demand($userMock, 'test', 'test', 'test');

        $command = new ApproveDemand($demand->uuid, $userMock);

        $externalServiceConfigMock
            ->expects(self::once())
            ->method('isUserEligible')
            ->with($userMock)
            ->willReturn(true)
        ;

        $this->externalServiceConfigRepoMock
            ->expects(self::once())
            ->method('getByName')
            ->with($demand->service)
            ->willReturn($externalServiceConfigMock)
        ;

        $this->demandRepositoryMock
            ->expects(self::once())
            ->method('getByUuid')
            ->with($demand->uuid)
            ->willReturn($demand)
        ;

        $this->demandRepositoryMock
            ->expects(self::once())
            ->method('save')
            ->with($demand)
        ;

        $this->domainEventPublisherMock
            ->expects(self::once())
            ->method('publish')
            ->with(self::isInstanceOf(DemandApproved::class))
        ;

        $this->handler->__invoke($command);
    }

    public function testApprovingNonExistingDemandWillThrowException(): void
    {
        $userMock = $this->createMock(User::class);
        $nonExistingDemandUuid = Uuid::uuid4();

        $command = new ApproveDemand($nonExistingDemandUuid, $userMock);

        $this->demandRepositoryMock
            ->expects(self::once())
            ->method('getByUuid')
            ->with($nonExistingDemandUuid)
            ->willThrowException(new DemandNotFoundException())
        ;

        $this->expectException(DemandNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testApprovingByIneligibleUserWillThrowException(): void
    {
        $externalServiceConfigMock = $this->createMock(ExternalServiceConfiguration::class);
        $user = new User(Email::fromString('test@local.host'), 'name');
        $demand = new Demand($user, 'test', 'test', 'test');
        $command = new ApproveDemand($demand->uuid, $user);

        $externalServiceConfigMock
            ->expects(self::once())
            ->method('isUserEligible')
            ->with($user)
            ->willReturn(false)
        ;

        $this->externalServiceConfigRepoMock
            ->expects(self::once())
            ->method('getByName')
            ->with($demand->service)
            ->willReturn($externalServiceConfigMock)
        ;
        $this->demandRepositoryMock
            ->expects(self::once())
            ->method('getByUuid')
            ->with($demand->uuid)
            ->willReturn($demand)
        ;
        $this->demandRepositoryMock
            ->expects(self::never())
            ->method('save')
        ;
        $this->domainEventPublisherMock
            ->expects(self::never())
            ->method('publish')
        ;

        $this->expectException(UserNotAuthorizedToUpdateDemandException::class);

        $this->handler->__invoke($command);
    }
}
