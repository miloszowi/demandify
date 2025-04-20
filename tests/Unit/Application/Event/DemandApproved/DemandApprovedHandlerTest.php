<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Event\DemandApproved;

use Demandify\Application\Command\CommandBus;
use Demandify\Application\Event\DemandApproved\DemandApprovedHandler;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\Event\DemandApproved;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DemandApprovedHandler::class)]
final class DemandApprovedHandlerTest extends TestCase
{
    private CommandBus|MockObject $commandBusMock;
    private DemandApprovedHandler $demandApprovedHandler;

    protected function setUp(): void
    {
        $this->commandBusMock = $this->createMock(CommandBus::class);
        $this->demandApprovedHandler = new DemandApprovedHandler($this->commandBusMock);
    }

    public function testDispatchesCommandsWhenDemandIsApproved(): void
    {
        $user = new User(Email::fromString('example@local.host'));
        $demand = new Demand($user, 'some_service', 'content', 'reason');
        $event = new DemandApproved($demand);

        $this->commandBusMock
            ->expects(self::exactly(3))
            ->method('dispatch')
            ->withAnyParameters()
        ;

        $this->demandApprovedHandler->__invoke($event);
    }
}
