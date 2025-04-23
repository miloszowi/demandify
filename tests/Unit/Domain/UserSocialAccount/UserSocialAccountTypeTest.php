<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Domain\UserSocialAccount;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UserSocialAccountType::class)]
final class UserSocialAccountTypeTest extends TestCase
{
    public function testIsEqualTo(): void
    {
        $slackType = UserSocialAccountType::SLACK;
        $googleType = UserSocialAccountType::GOOGLE;

        self::assertTrue($slackType->isEqualTo(UserSocialAccountType::SLACK));
        self::assertFalse($slackType->isEqualTo($googleType));
    }

    public function testFromString(): void
    {
        self::assertSame(UserSocialAccountType::SLACK, UserSocialAccountType::fromString('slack'));
        self::assertSame(UserSocialAccountType::GOOGLE, UserSocialAccountType::fromString('google'));
    }

    public function testFromStringCaseInsensitive(): void
    {
        self::assertSame(UserSocialAccountType::SLACK, UserSocialAccountType::fromString('SLACK'));
        self::assertSame(UserSocialAccountType::GOOGLE, UserSocialAccountType::fromString('GoOgLe'));
    }
}
