<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\ExecuteDemand;

use Demandify\Application\Command\ExecuteDemand\ExecuteDemand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(ExecuteDemand::class)]
final class ExecuteDemandTest extends TestCase
{
    public function testItIsInitializable(): void
    {
        $demandUuid = Uuid::fromString('12345678-1234-1234-1234-123456789012');
        $command = new ExecuteDemand($demandUuid);

        self::assertInstanceOf(ExecuteDemand::class, $command);
        self::assertSame($demandUuid, $command->demandUuid);
    }
}
