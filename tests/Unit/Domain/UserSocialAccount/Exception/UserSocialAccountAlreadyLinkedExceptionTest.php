<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\UserSocialAccount\Exception;

use Demandify\Domain\UserSocialAccount\Exception\UserSocialAccountAlreadyLinkedException;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Guid\Guid;

/**
 * @internal
 */
#[CoversClass(UserSocialAccountAlreadyLinkedException::class)]
final class UserSocialAccountAlreadyLinkedExceptionTest extends TestCase
{
    public function testItCanBeCreatedFromPair(): void
    {
        $userUuid = Guid::uuid4();
        $socialAccountType = UserSocialAccountType::SLACK;

        $exception = UserSocialAccountAlreadyLinkedException::fromPair($userUuid, $socialAccountType);

        self::assertInstanceOf(UserSocialAccountAlreadyLinkedException::class, $exception);
        self::assertSame(
            'User with id of "'.$userUuid->toString().'" has already linked social account of type "slack"',
            $exception->getMessage()
        );
    }
}
