<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\Demand\Exception;

use Demandify\Domain\Demand\Exception\InvalidDemandStatusException;
use Demandify\Domain\Demand\Status;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(InvalidDemandStatusException::class)]
final class InvalidDemandStatusExceptionTest extends TestCase
{
    public function testItCreatesExceptionForApprovingWithWrongStatus(): void
    {
        $status = Status::APPROVED;
        $exception = InvalidDemandStatusException::forApproving($status);

        self::assertInstanceOf(InvalidDemandStatusException::class, $exception);
        self::assertSame(
            \sprintf(
                'Can not approve demand with status "%s", only "%s" status is allowed.',
                $status->value,
                Status::NEW->value
            ),
            $exception->getMessage()
        );
    }

    public function testItCreatesExceptionForDecliningWithWrongStatus(): void
    {
        $status = Status::DECLINED;
        $exception = InvalidDemandStatusException::forDeclining($status);

        self::assertInstanceOf(InvalidDemandStatusException::class, $exception);
        self::assertSame(
            \sprintf(
                'Can not decline demand with status "%s", only "%s" status is allowed.',
                $status->value,
                Status::NEW->value
            ),
            $exception->getMessage()
        );
    }
}
