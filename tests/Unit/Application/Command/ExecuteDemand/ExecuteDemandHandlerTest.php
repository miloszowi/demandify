<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\ExecuteDemand;

use Demandify\Application\Command\ExecuteDemand\ExecuteDemand;
use Demandify\Application\Command\ExecuteDemand\ExecuteDemandHandler;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\Demand\Exception\DemandNotFoundException;
use Demandify\Domain\Task\DemandExecutor;
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

    protected function setUp(): void
    {
        $this->demandExecutor = $this->createMock(DemandExecutor::class);
        $this->demandRepository = $this->createMock(DemandRepository::class);

        $this->handler = new ExecuteDemandHandler(
            $this->demandExecutor,
            $this->demandRepository,
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

        $this->demandRepository
            ->expects(self::exactly(2))
            ->method('save')
            ->with($demandMock)
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

        $this->handler->__invoke(new ExecuteDemand($demandUuid));
    }

    public function testItThrowsExceptionIfDemandDoesNotExist(): void
    {
        $demandUuid = Uuid::uuid4();

        $this->demandRepository
            ->expects(self::once())
            ->method('getByUuid')
            ->with($demandUuid)
            ->willThrowException(DemandNotFoundException::fromUuid($demandUuid))
        ;

        $this->demandRepository
            ->expects(self::never())
            ->method('save')
        ;

        self::expectException(DemandNotFoundException::class);

        $this->handler->__invoke(new ExecuteDemand($demandUuid));
    }
}
