<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\ApproveDemand;

use Demandify\Application\Command\ApproveDemand\ApproveDemand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(ApproveDemand::class)]
final class ApproveDemandTest extends TestCase
{
    public function testIsInitializable(): void
    {
        $demandUuid = Uuid::fromString('12345678-1234-1234-1234-123456789012');
        $approverUuid = Uuid::fromString('3896c48a-16ef-495b-96ab-75087b09037c');

        $command = new ApproveDemand($demandUuid, $approverUuid);

        self::assertInstanceOf(ApproveDemand::class, $command);
        self::assertSame($demandUuid, $command->demandUuid);
        self::assertSame($approverUuid, $command->approverUuid);
    }
}
