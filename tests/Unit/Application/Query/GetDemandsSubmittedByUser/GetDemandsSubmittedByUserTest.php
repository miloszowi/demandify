<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Query\GetDemandsSubmittedByUser;

use Demandify\Application\Query\GetDemandsSubmittedByUser\GetDemandsSubmittedByUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(GetDemandsSubmittedByUser::class)]
final class GetDemandsSubmittedByUserTest extends TestCase
{
    public function testIsInitializable(): void
    {
        $query = new GetDemandsSubmittedByUser(
            Uuid::uuid4(),
            1,
            10,
            'search'
        );

        self::assertInstanceOf(GetDemandsSubmittedByUser::class, $query);
    }
}
