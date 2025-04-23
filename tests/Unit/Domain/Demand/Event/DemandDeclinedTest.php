<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\Demand\Event;

use Demandify\Domain\Demand\Event\DemandDeclined;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(DemandDeclined::class)]
final class DemandDeclinedTest extends TestCase
{
    private DemandDeclined $event;

    protected function setUp(): void
    {
        $this->event = new DemandDeclined(Uuid::uuid4());
    }

    public function testItIsInitializable(): void
    {
        self::assertInstanceOf(DemandDeclined::class, $this->event);
    }

    public function testItHasOccuredOn(): void
    {
        self::assertGreaterThan($this->event->occuredAt(), new \DateTimeImmutable());
    }
}
