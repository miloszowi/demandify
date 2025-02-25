<?php

declare(strict_types=1);

namespace Demandify\Tests\Integration\Application\Command\ExecuteDemand;

use Demandify\Application\Command\ExecuteDemand\ExecuteDemandHandler;
use Demandify\Tests\Integration\BaseKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

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
