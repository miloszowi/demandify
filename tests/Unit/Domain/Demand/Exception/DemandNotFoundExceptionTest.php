<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\Demand\Exception;

use Demandify\Domain\Demand\Exception\DemandNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(DemandNotFoundException::class)]
final class DemandNotFoundExceptionTest extends TestCase
{
    public function testItCreatesExceptionWithCorrectMessage(): void
    {
        $uuid = Uuid::uuid4();
        $exception = DemandNotFoundException::fromUuid($uuid);

        self::assertInstanceOf(DemandNotFoundException::class, $exception);
        self::assertSame(
            \sprintf('Demand with uuid of "%s" was not found.', $uuid->toString()),
            $exception->getMessage()
        );
    }
}
