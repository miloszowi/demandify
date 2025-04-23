<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Application\Query\GetDemandsToBeReviewedForUser;

use Demandify\Application\Query\GetDemandsToBeReviewedForUser\GetDemandsToBeReviewedForUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(GetDemandsToBeReviewedForUser::class)]
final class GetDemandsToBeReviewedForUserTest extends TestCase
{
    public function testIsInitializable(): void
    {
        $query = new GetDemandsToBeReviewedForUser(Uuid::uuid4());

        self::assertInstanceOf(GetDemandsToBeReviewedForUser::class, $query);
    }
}
