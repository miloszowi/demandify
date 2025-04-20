<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\ApproveDemand;

use Demandify\Application\Command\ApproveDemand\ApproveDemand;
use Demandify\Application\Command\ApproveDemand\ApproveDemandHandler;
use Demandify\Application\Query\QueryBus;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Exception\DemandNotFoundException;
use Demandify\Domain\Demand\Exception\UserNotAuthorizedToUpdateDemandException;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(ApproveDemandHandler::class)]
final class ApproveDemandHandlerTest extends TestCase
{
    private DemandRepository|MockObject $demandRepositoryMock;
    private MockObject|QueryBus $queryBus;
    private ApproveDemandHandler $handler;

    protected function setUp(): void
    {
        $this->demandRepositoryMock = $this->createMock(DemandRepository::class);
        $this->queryBus = $this->createMock(QueryBus::class);

        $this->handler = new ApproveDemandHandler(
            $this->demandRepositoryMock,
            $this->queryBus
        );
    }

    public function testIsInitializable(): void
    {
        self::assertInstanceOf(ApproveDemandHandler::class, $this->handler);
    }

    public function testApprovesDemand(): void
    {
        $userMock = $this->createMock(User::class);
        $demand = new Demand($userMock, 'test', 'test', 'test');
        $command = new ApproveDemand($demand->uuid, $userMock);

        $this->queryBus
            ->expects(self::once())
            ->method('ask')
            ->willReturn(true)
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
        $user = new User(Email::fromString('test@local.host'));
        $demand = new Demand($user, 'test', 'test', 'test');
        $command = new ApproveDemand($demand->uuid, $user);

        $this->demandRepositoryMock
            ->expects(self::once())
            ->method('getByUuid')
            ->with($demand->uuid)
            ->willReturn($demand)
        ;

        $this->queryBus
            ->expects(self::once())
            ->method('ask')
            ->willReturn(false)
        ;

        $this->demandRepositoryMock
            ->expects(self::never())
            ->method('save')
        ;

        $this->expectException(UserNotAuthorizedToUpdateDemandException::class);

        $this->handler->__invoke($command);
    }
}
