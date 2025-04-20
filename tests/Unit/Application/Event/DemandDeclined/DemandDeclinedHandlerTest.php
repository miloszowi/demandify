<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Event\DemandDeclined;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Event\DemandDeclined\DemandDeclinedHandler;
use Demandify\Domain\Demand\Demand;
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
    private CommandBus|MockObject $commandBusMock;
    private DemandDeclinedHandler $demandDeclinedHandler;

    protected function setUp(): void
    {
        $this->commandBusMock = $this->createMock(CommandBus::class);
        $this->demandDeclinedHandler = new DemandDeclinedHandler($this->commandBusMock);
    }

    public function testDispatchesCommandsWhenDemandIsDeclined(): void
    {
        $user = new User(Email::fromString('example@local.host'));
        $demand = new Demand($user, 'some_service', 'content', 'reason');
        $event = new DemandDeclined($demand);

        $this->commandBusMock
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->withAnyParameters()
        ;

        $this->demandDeclinedHandler->__invoke($event);
    }
}
