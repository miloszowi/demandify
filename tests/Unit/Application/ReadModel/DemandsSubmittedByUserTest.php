<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\ReadModel;

use Demandify\Application\Query\ReadModel\DemandsSubmittedByUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DemandsSubmittedByUser::class)]
final class DemandsSubmittedByUserTest extends TestCase
{
    public function testItIsInitializable(): void
    {
        $demandsSubmittedByUser = new DemandsSubmittedByUser(
            ['test_demand_key' => 'test_demand_value'],
            1,
            1,
            10,
            1,
            'search'
        );

        self::assertInstanceOf(DemandsSubmittedByUser::class, $demandsSubmittedByUser);
    }
}
