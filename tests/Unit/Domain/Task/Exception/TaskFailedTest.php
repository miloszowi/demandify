<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\Task\Event;

use Demandify\Domain\Task\Event\TaskFailed;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(TaskFailed::class)]
final class TaskFailedTest extends TestCase
{
    private TaskFailed $event;

    protected function setUp(): void
    {
        $this->event = new TaskFailed(Uuid::uuid4());
    }

    public function testItIsInitializable(): void
    {
        self::assertInstanceOf(TaskFailed::class, $this->event);
    }

    public function testItHasOccuredAt(): void
    {
        self::assertGreaterThan($this->event->occuredAt(), new \DateTimeImmutable());
    }
}
