<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Event\TaskSucceeded;

use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Event\TaskSucceeded\TaskSucceededHandler;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Task\Event\TaskSucceeded;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use Demandify\Infrastructure\Symfony\Messenger\CommandBus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TaskSucceededHandler::class)]
final class TaskSucceededHandlerTest extends TestCase
{
    private TaskSucceededHandler $handler;
    private DemandRepository|MockObject $demandRepositoryMock;
    private CommandBus|MockObject $commandBus;

    protected function setUp(): void
    {
        $this->demandRepositoryMock = $this->createMock(DemandRepository::class);
        $this->commandBus = $this->createMock(CommandBus::class);
        $this->handler = new TaskSucceededHandler($this->demandRepositoryMock, $this->commandBus);
    }

    public function testItSendsCommandToCommandBus(): void
    {
        $demand = new Demand(
            new User(Email::fromString('user@local.host')),
            'some_service',
            'some_content',
            'some_reason',
        );

        $this->demandRepositoryMock
            ->expects(self::once())
            ->method('getByUuid')
            ->with($demand->uuid)
            ->willReturn($demand)
        ;

        $this->commandBus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(SendDemandNotification::class))
        ;

        $this->handler->__invoke(new TaskSucceeded($demand->uuid));
    }
}
