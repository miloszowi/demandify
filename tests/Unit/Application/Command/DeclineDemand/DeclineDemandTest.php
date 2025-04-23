<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\DeclineDemand;

use Demandify\Application\Command\DeclineDemand\DeclineDemand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(DeclineDemand::class)]
final class DeclineDemandTest extends TestCase
{
    public function testIsInitializable(): void
    {
        $demandUuid = Uuid::fromString('12345678-1234-1234-1234-123456789012');
        $approverUuid = Uuid::fromString('3896c48a-16ef-495b-96ab-75087b09037c');

        $command = new DeclineDemand($demandUuid, $approverUuid);

        self::assertInstanceOf(DeclineDemand::class, $command);
        self::assertSame($demandUuid, $command->demandUuid);
        self::assertSame($approverUuid, $command->approverUuid);
    }
}
