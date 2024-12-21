<?php

declare(strict_types=1);

namespace Querify\Tests\Integration\Application\Command\ExecuteDemand;

use PHPUnit\Framework\Attributes\CoversClass;
use Querify\Application\Command\ExecuteDemand\ExecuteDemandHandler;
use Querify\Tests\Integration\BaseKernelTestCase;

/**
 * @internal
 */
#[CoversClass(ExecuteDemandHandler::class)]
final class ExecuteDemandHandlerTest extends BaseKernelTestCase
{
    public function testToBeFilled(): void
    {
        self::markTestIncomplete('This test has not been implemented yet.');
    }
}
