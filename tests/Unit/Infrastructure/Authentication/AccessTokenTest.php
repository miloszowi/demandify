<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Infrastructure\Authentication;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Authentication\AccessToken;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AccessToken::class)]
final class AccessTokenTest extends TestCase
{
    public function testItCreatesAccessTokenWithGivenValues(): void
    {
        $type = UserSocialAccountType::SLACK;
        $email = 'user@example.com';
        $value = 'token_value_123';
        $expiresIn = 3600;

        $token = new AccessToken($type, $email, $value, $expiresIn);

        self::assertInstanceOf(AccessToken::class, $token);
        self::assertSame($type, $token->type);
        self::assertSame($email, $token->email);
        self::assertSame($value, $token->value);
        self::assertSame($expiresIn, $token->expiresIn);
    }
}
