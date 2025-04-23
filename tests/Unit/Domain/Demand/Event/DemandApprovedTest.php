<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\Demand\Event;

use Demandify\Domain\Demand\Event\DemandApproved;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(DemandApproved::class)]
final class DemandApprovedTest extends TestCase
{
    private DemandApproved $event;

    protected function setUp(): void
    {
        $this->event = new DemandApproved(Uuid::uuid4());
    }

    public function testItIsInitializable(): void
    {
        self::assertInstanceOf(DemandApproved::class, $this->event);
    }

    public function testItHasOccuredOn(): void
    {
        self::assertGreaterThan($this->event->occuredAt(), new \DateTimeImmutable());
    }
}
