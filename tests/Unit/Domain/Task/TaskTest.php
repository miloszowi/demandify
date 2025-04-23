<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\Task;

use Demandify\Domain\Task\Task;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(Task::class)]
final class TaskTest extends TestCase
{
    private Task $task;

    protected function setUp(): void
    {
        $this->task = new Task(
            success: true,
            executionTime: 120,
            errorMessage: null,
            resultPath: 'some/path/to/result'
        );
    }

    public function testItIsInitializable(): void
    {
        self::assertInstanceOf(Task::class, $this->task);
    }

    public function testItHasUuid(): void
    {
        self::assertTrue(Uuid::isValid($this->task->uuid->toString()));
    }

    public function testItHasExecutedAt(): void
    {
        self::assertInstanceOf(\DateTimeImmutable::class, $this->task->executedAt);
        self::assertGreaterThan($this->task->executedAt, new \DateTimeImmutable());
    }

    public function testItHasSuccess(): void
    {
        self::assertTrue($this->task->success);
    }

    public function testItHasExecutionTime(): void
    {
        self::assertSame(120, $this->task->executionTime);
    }

    public function testItHasErrorMessage(): void
    {
        self::assertNull($this->task->errorMessage);
    }

    public function testItHasResultPath(): void
    {
        self::assertSame('some/path/to/result', $this->task->resultPath);
    }
}
