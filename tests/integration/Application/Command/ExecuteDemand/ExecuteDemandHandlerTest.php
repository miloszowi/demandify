<?php

declare(strict_types=1);

namespace Querify\Tests\Integration\Application\Command\ExecuteDemand;

use Querify\Tests\integration\BaseKernelTestCase;

/**
 * @internal
 *
 * @covers \Querify\Application\Command\ExecuteDemand\ExecuteDemandHandler
 */
final class ExecuteDemandHandlerTest extends BaseKernelTestCase
{
    public function testToBeFilled(): void
    {
        self::assertCount(0, []);
    }
}
