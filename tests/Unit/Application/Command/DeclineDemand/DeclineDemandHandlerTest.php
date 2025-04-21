<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\DeclineDemand;

use Demandify\Application\Command\DeclineDemand\DeclineDemand;
use Demandify\Application\Command\DeclineDemand\DeclineDemandHandler;
use Demandify\Application\Query\QueryBus;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Exception\DemandNotFoundException;
use Demandify\Domain\Demand\Exception\UserNotAuthorizedToUpdateDemandException;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use Demandify\Domain\User\UserRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(DeclineDemandHandler::class)]
final class DeclineDemandHandlerTest extends TestCase
{
    private DemandRepository|MockObject $demandRepositoryMock;
    private MockObject|UserRepository $userRepository;
    private MockObject|QueryBus $queryBus;
    private DeclineDemandHandler $handler;

    protected function setUp(): void
    {
        $this->demandRepositoryMock = $this->createMock(DemandRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->queryBus = $this->createMock(QueryBus::class);

        $this->handler = new DeclineDemandHandler(
            $this->demandRepositoryMock,
            $this->userRepository,
            $this->queryBus,
        );
    }

    public function testIsInitializable(): void
    {
        self::assertInstanceOf(DeclineDemandHandler::class, $this->handler);
    }

    public function testDeclinesDemand(): void
    {
        $user = new User(Email::fromString('test@local.host'));
        $demand = new Demand($user, 'test', 'test', 'test');
        $command = new DeclineDemand($demand->uuid, $user->uuid);

        $this->demandRepositoryMock
            ->expects(self::once())
            ->method('getByUuid')
            ->with($demand->uuid)
            ->willReturn($demand)
        ;

        $this->userRepository
            ->expects(self::once())
            ->method('getByUuid')
            ->with($user->uuid)
            ->willReturn($user)
        ;

        $this->queryBus
            ->expects(self::once())
            ->method('ask')
            ->willReturn(true)
        ;

        $this->demandRepositoryMock
            ->expects(self::once())
            ->method('save')
            ->with($demand)
        ;

        $this->handler->__invoke($command);
    }

    public function testDecliningNonExistingDemandWillThrowException(): void
    {
        $nonExistingUuid = Uuid::uuid4();

        $command = new DeclineDemand($nonExistingUuid, Uuid::uuid4());

        $this->demandRepositoryMock
            ->expects(self::once())
            ->method('getByUuid')
            ->with($nonExistingUuid)
            ->willThrowException(new DemandNotFoundException())
        ;

        $this->expectException(DemandNotFoundException::class);

        $this->handler->__invoke($command);
    }

    public function testDecliningByIneligibleUserWillThrowException(): void
    {
        $user = new User(Email::fromString('test@local.host'));
        $demand = new Demand($user, 'test', 'test', 'test');
        $command = new DeclineDemand($demand->uuid, $user->uuid);

        $this->demandRepositoryMock
            ->expects(self::once())
            ->method('getByUuid')
            ->with($demand->uuid)
            ->willReturn($demand)
        ;

        $this->userRepository
            ->expects(self::once())
            ->method('getByUuid')
            ->with($user->uuid)
            ->willReturn($user)
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
