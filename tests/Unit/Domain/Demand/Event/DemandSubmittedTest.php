<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\Demand\Event;

use Demandify\Domain\Demand\Event\DemandSubmitted;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(DemandSubmitted::class)]
final class DemandSubmittedTest extends TestCase
{
    private DemandSubmitted $event;

    protected function setUp(): void
    {
        $this->event = new DemandSubmitted(Uuid::uuid4());
    }

    public function testItIsInitializable(): void
    {
        self::assertInstanceOf(DemandSubmitted::class, $this->event);
    }

    public function testItHasOccuredOn(): void
    {
        self::assertGreaterThan($this->event->occuredAt(), new \DateTimeImmutable());
    }
}
