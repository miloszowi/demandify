<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Command\SubmitDemand;

use Demandify\Application\Command\SubmitDemand\SubmitDemand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SubmitDemand::class)]
final class SubmitDemandTest extends TestCase
{
    public function testIsInitializable(): void
    {
        $command = new SubmitDemand(
            'test-email@local.host',
            'test-service',
            'test-content',
            'test-reason'
        );

        self::assertInstanceOf(SubmitDemand::class, $command);
    }
}
