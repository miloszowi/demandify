<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\User;

use Demandify\Domain\User\UserRole;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UserRole::class)]
final class UserRoleTest extends TestCase
{
    public function testItReturnsAllRoleNamesAsArray(): void
    {
        $expectedRoles = [
            'ROLE_USER',
            'ROLE_ADMIN',
        ];

        $roles = UserRole::asArray();

        self::assertSame($expectedRoles, $roles);
    }
}
