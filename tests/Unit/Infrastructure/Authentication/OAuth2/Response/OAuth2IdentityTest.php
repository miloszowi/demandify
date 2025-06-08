<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Infrastructure\Authentication\OAuth2\Response;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Authentication\AccessToken;
use Demandify\Infrastructure\Authentication\OAuth2\Response\OAuth2Identity;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class OAuth2IdentityTest extends TestCase
{
    public function testItStoresAllProvidedData(): void
    {
        $type = UserSocialAccountType::GOOGLE;
        $accessToken = $this->createMock(AccessToken::class);
        $email = 'user@local.host';
        $externalUserId = 'external-123';
        $extraData = ['name' => 'John Doe', 'avatar' => 'http://example.com/avatar.jpg'];

        $identity = new OAuth2Identity(
            $type,
            $accessToken,
            $email,
            $externalUserId,
            $extraData
        );

        self::assertSame($type, $identity->type);
        self::assertSame($accessToken, $identity->accessToken);
        self::assertSame($email, $identity->email);
        self::assertSame($externalUserId, $identity->externalUserId);
        self::assertSame($extraData, $identity->extraData);
    }
}
