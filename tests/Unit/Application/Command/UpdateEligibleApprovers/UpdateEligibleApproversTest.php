<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\UpdateEligibleApprovers;

use Demandify\Application\Command\UpdateEligibleApprovers\UpdateEligibleApprovers;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(UpdateEligibleApprovers::class)]
final class UpdateEligibleApproversTest extends TestCase
{
    public function testIsInitializable(): void
    {
        $command = new UpdateEligibleApprovers(
            'test-service-name',
            [Uuid::uuid4()]
        );

        self::assertInstanceOf(UpdateEligibleApprovers::class, $command);
    }
}
