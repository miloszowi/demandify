<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\UserSocialAccount\Exception;

use Demandify\Domain\UserSocialAccount\Exception\UserSocialAccountNotFound;
use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 */
#[CoversClass(UserSocialAccountNotFound::class)]
final class UserSocialAccountNotFoundTest extends TestCase
{
    public function testItCanBeCreatedFromExternalIdAndType(): void
    {
        $externalId = '123456';
        $socialAccountType = UserSocialAccountType::SLACK;

        $exception = UserSocialAccountNotFound::fromExternalIdAndType($externalId, $socialAccountType);

        self::assertInstanceOf(UserSocialAccountNotFound::class, $exception);
        self::assertSame(
            'User social account with external id of "123456" of type "slack" was not found.',
            $exception->getMessage()
        );
    }

    public function testItCanBeCreatedFromUserUuidIdAndType(): void
    {
        $userUuid = Uuid::uuid4();
        $socialAccountType = UserSocialAccountType::GOOGLE;

        $exception = UserSocialAccountNotFound::fromUserUuidIdAndType($userUuid, $socialAccountType);

        self::assertInstanceOf(UserSocialAccountNotFound::class, $exception);
        self::assertSame(
            'User social account for user uuid of "'.$userUuid->toString().'" and type "google" was not found.',
            $exception->getMessage()
        );
    }
}
