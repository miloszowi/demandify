<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\Task\Event;

use Demandify\Domain\Task\Event\TaskSucceeded;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(TaskSucceeded::class)]
final class TaskSucceededTest extends TestCase
{
    private TaskSucceeded $event;

    protected function setUp(): void
    {
        $this->event = new TaskSucceeded(Uuid::uuid4());
    }

    public function testItIsInitializable(): void
    {
        self::assertInstanceOf(TaskSucceeded::class, $this->event);
    }

    public function testItHasOccuredAt(): void
    {
        self::assertGreaterThan($this->event->occuredAt(), new \DateTimeImmutable());
    }
}
