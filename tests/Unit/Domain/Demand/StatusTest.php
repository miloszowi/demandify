<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\Demand;

use Demandify\Domain\Demand\Exception\InvalidDemandStatusException;
use Demandify\Domain\Demand\Status;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Status::class)]
final class StatusTest extends TestCase
{
    public function testAsArray(): void
    {
        $expected = ['NEW', 'APPROVED', 'IN_PROGRESS', 'FAILED', 'DECLINED', 'EXECUTED'];
        self::assertSame($expected, Status::asArray());
    }

    public function testIsEqualTo(): void
    {
        $status = Status::NEW;
        self::assertTrue($status->isEqualTo(Status::NEW));
        self::assertFalse($status->isEqualTo(Status::APPROVED));
    }

    public function testProgressValidTransition(): void
    {
        $status = Status::NEW;
        $newStatus = $status->progress(Status::APPROVED);
        self::assertSame(Status::APPROVED, $newStatus);
    }

    public function testProgressNoTransitionsAvailable(): void
    {
        $status = Status::EXECUTED;
        self::expectException(InvalidDemandStatusException::class);
        self::expectExceptionMessage('No transitions allowed from status EXECUTED.');
        $status->progress(Status::APPROVED);
    }

    public function testProgressInvalidTransition(): void
    {
        $status = Status::NEW;
        self::expectException(InvalidDemandStatusException::class);
        self::expectExceptionMessage('Invalid transition from status NEW to IN_PROGRESS.');
        $status->progress(Status::IN_PROGRESS);
    }

    public function testIsInApprovedFlow(): void
    {
        self::assertTrue(Status::APPROVED->isInApprovedFlow());
        self::assertFalse(Status::NEW->isInApprovedFlow());
    }

    public function testIsDeclined(): void
    {
        self::assertTrue(Status::DECLINED->isDeclined());
        self::assertFalse(Status::NEW->isDeclined());
    }
}
