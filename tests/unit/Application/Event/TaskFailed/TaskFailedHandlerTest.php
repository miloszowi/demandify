<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Event\TaskFailed;

use Demandify\Application\Command\SendDemandNotification\SendDemandNotification;
use Demandify\Application\Event\TaskFailed\TaskFailedHandler;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Task\Event\TaskFailed;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use Demandify\Infrastructure\Symfony\Messenger\CommandBus;
use Monolog\Test\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
#[CoversClass(TaskFailedHandler::class)]
final class TaskFailedHandlerTest extends TestCase
{
    private TaskFailedHandler $handler;
    private CommandBus|MockObject $commandBus;

    protected function setUp(): void
    {
        $this->commandBus = $this->createMock(CommandBus::class);
        $this->handler = new TaskFailedHandler($this->commandBus);
    }

    public function testItSendsCommandToCommandBus(): void
    {
        $demand = new Demand(
            new User(Email::fromString('user@local.host')),
            'some_service',
            'some_content',
            'some_reason',
        );
        $this->commandBus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(SendDemandNotification::class))
        ;

        $this->handler->__invoke(new TaskFailed($demand));
    }
}
