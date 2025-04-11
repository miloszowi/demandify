<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\ExecuteDemand;

use Demandify\Application\Command\ExecuteDemand\ExecuteDemand;
use Demandify\Application\Command\ExecuteDemand\ExecuteDemandHandler;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\DomainEventPublisher;
use Demandify\Domain\Task\DemandExecutor;
use Demandify\Domain\Task\Event\TaskSucceeded;
use Demandify\Domain\Task\Task;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(ExecuteDemandHandler::class)]
final class ExecuteDemandHandlerTest extends TestCase
{
    private ExecuteDemandHandler $handler;
    private DemandExecutor|MockObject $demandExecutor;
    private DemandRepository|MockObject $demandRepository;
    private DomainEventPublisher|MockObject $domainEventPublisher;

    protected function setUp(): void
    {
        $this->demandExecutor = $this->createMock(DemandExecutor::class);
        $this->demandRepository = $this->createMock(DemandRepository::class);
        $this->domainEventPublisher = $this->createMock(DomainEventPublisher::class);

        $this->handler = new ExecuteDemandHandler(
            $this->demandExecutor,
            $this->demandRepository,
            $this->domainEventPublisher,
        );
    }

    public function testExecuteDemand(): void
    {
        $demandUuid = Uuid::uuid4();

        $demandMock = $this->createMock(Demand::class);
        $demandMock->task = new Task(true, 1);

        $this->demandRepository
            ->expects(self::once())
            ->method('getByUuid')
            ->with($demandUuid)
            ->willReturn($demandMock)
        ;

        $demandMock
            ->expects(self::once())
            ->method('start')
        ;

        $demandMock
            ->expects(self::once())
            ->method('execute')
            ->with($this->demandExecutor)
        ;

        $this->domainEventPublisher
            ->expects(self::once())
            ->method('publish')
            ->with(self::isInstanceOf(TaskSucceeded::class))
        ;

        $this->handler->__invoke(new ExecuteDemand($demandUuid));
    }
}
