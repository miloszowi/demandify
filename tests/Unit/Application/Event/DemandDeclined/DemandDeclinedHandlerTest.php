<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Event\DemandDeclined;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Event\DemandDeclined\DemandDeclinedHandler;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Event\DemandDeclined;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DemandDeclinedHandler::class)]
final class DemandDeclinedHandlerTest extends TestCase
{
    private DemandRepository|MockObject $demandRepositoryMock;
    private CommandBus|MockObject $commandBusMock;
    private DemandDeclinedHandler $demandDeclinedHandler;

    protected function setUp(): void
    {
        $this->demandRepositoryMock = $this->createMock(DemandRepository::class);
        $this->commandBusMock = $this->createMock(CommandBus::class);
        $this->demandDeclinedHandler = new DemandDeclinedHandler($this->demandRepositoryMock, $this->commandBusMock);
    }

    public function testDispatchesCommandsWhenDemandIsDeclined(): void
    {
        $user = new User(Email::fromString('example@local.host'));
        $demand = new Demand($user, 'some_service', 'content', 'reason');
        $event = new DemandDeclined($demand->uuid);

        $this->demandRepositoryMock
            ->expects(self::once())
            ->method('getByUuid')
            ->with($demand->uuid)
            ->willReturn($demand)
        ;

        $this->commandBusMock
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->withAnyParameters()
        ;

        $this->demandDeclinedHandler->__invoke($event);
    }
}
