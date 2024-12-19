<?php

declare(strict_types=1);

namespace Querify\Tests\Unit\Application\Command\DeclineDemand;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Querify\Application\Command\DeclineDemand\DeclineDemand;
use Querify\Application\Command\DeclineDemand\DeclineDemandHandler;
use Querify\Domain\Demand\Demand;
use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\Demand\Exception\DemandNotFoundException;
use Querify\Domain\Demand\Exception\UserNotAuthorizedToUpdateDemandException;
use Querify\Domain\DomainEvent;
use Querify\Domain\DomainEventPublisher;
use Querify\Domain\ExternalService\ExternalServiceConfiguration;
use Querify\Domain\ExternalService\ExternalServiceConfigurationRepository;
use Querify\Domain\User\Email;
use Querify\Domain\User\User;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(DeclineDemandHandler::class)]
final class DeclineDemandHandlerTest extends TestCase
{
    private DemandRepository|MockObject $demandRepositoryMock;
    private DomainEventPublisher|MockObject $domainEventPublisherMock;
    private ExternalServiceConfigurationRepository|MockObject $externalServiceConfigRepoMock;
    private DeclineDemandHandler $handler;

    protected function setUp(): void
    {
        $this->demandRepositoryMock = $this->createMock(DemandRepository::class);
        $this->domainEventPublisherMock = $this->createMock(DomainEventPublisher::class);
        $this->externalServiceConfigRepoMock = $this->createMock(ExternalServiceConfigurationRepository::class);

        $this->handler = new DeclineDemandHandler(
            $this->demandRepositoryMock,
            $this->domainEventPublisherMock,
            $this->externalServiceConfigRepoMock
        );
    }

    public function testIsInitializable(): void
    {
        self::assertInstanceOf(DeclineDemandHandler::class, $this->handler);
    }

    public function testDeclinesDemandAndPublishesEvent(): void
    {
        $userMock = $this->createMock(User::class);
        $externalServiceConfigMock = $this->createMock(ExternalServiceConfiguration::class);
        $demand = new Demand($userMock, 'test', 'test', 'test');

        $command = new DeclineDemand($demand->uuid, $userMock);

        $externalServiceConfigMock->expects(self::once())
            ->method('isUserEligible')
            ->with($userMock)
            ->willReturn(true)
        ;

        $this->externalServiceConfigRepoMock->expects(self::once())
            ->method('getByName')
            ->with($demand->service)
            ->willReturn($externalServiceConfigMock)
        ;

        $this->demandRepositoryMock->expects(self::once())
            ->method('getByUuid')
            ->with($demand->uuid)
            ->willReturn($demand)
        ;

        $this->demandRepositoryMock->expects(self::once())
            ->method('save')
            ->with($demand)
        ;

        $this->domainEventPublisherMock->expects(self::once())
            ->method('publish')
            ->with(self::isInstanceOf(DomainEvent::class))
        ;

        $this->handler->__invoke($command);
    }

    public function testThrowsExceptionIfDemandNotFound(): void
    {
        $userMock = $this->createMock(User::class);
        $nonExistingUuid = Uuid::uuid4();

        $command = new DeclineDemand($nonExistingUuid, $userMock);

        $this->demandRepositoryMock->expects(self::once())
            ->method('getByUuid')
            ->with($nonExistingUuid)
            ->willThrowException(new DemandNotFoundException())
        ;

        $this->expectException(DemandNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testThrowsExceptionIfUserIsNotEligible(): void
    {
        $externalServiceConfigMock = $this->createMock(ExternalServiceConfiguration::class);
        $user = new User(Email::fromString('test@local.host'), 'name');
        $demand = new Demand($user, 'test', 'test', 'test');
        $command = new DeclineDemand($demand->uuid, $user);

        $externalServiceConfigMock->expects(self::once())
            ->method('isUserEligible')
            ->with($user)
            ->willReturn(false)
        ;

        $this->externalServiceConfigRepoMock->expects(self::once())
            ->method('getByName')
            ->with($demand->service)
            ->willReturn($externalServiceConfigMock)
        ;

        $this->demandRepositoryMock->expects(self::once())
            ->method('getByUuid')
            ->with($demand->uuid)
            ->willReturn($demand)
        ;

        $this->demandRepositoryMock->expects(self::never())
            ->method('save')
        ;

        $this->domainEventPublisherMock->expects(self::never())
            ->method('publish')
        ;

        $this->expectException(UserNotAuthorizedToUpdateDemandException::class);

        $this->handler->__invoke($command);
    }
}
