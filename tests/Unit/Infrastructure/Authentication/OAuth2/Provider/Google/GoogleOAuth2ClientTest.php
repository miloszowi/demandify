<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Infrastructure\Authentication\OAuth2\Provider\Google;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Authentication\AccessToken;
use Demandify\Infrastructure\Authentication\OAuth2\Provider\Google\GoogleOAuth2Client;
use Demandify\Infrastructure\Authentication\OAuth2\Response\OAuth2Identity;
use Demandify\Infrastructure\External\Google\GoogleConfiguration;
use Demandify\Infrastructure\External\Google\Http\Exception\GoogleApiException;
use Demandify\Infrastructure\External\Google\Http\GoogleHttpClient;
use Demandify\Infrastructure\External\Google\Http\Response\OAuth2AccessResponse;
use Demandify\Infrastructure\External\Google\Http\Response\UserInfoResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GoogleOAuth2Client::class)]
final class GoogleOAuth2ClientTest extends TestCase
{
    private GoogleOAuth2Client $googleOAuth2Client;
    private GoogleConfiguration|MockObject $googleConfiguration;
    private GoogleHttpClient|MockObject $googleHttpClient;

    protected function setUp(): void
    {
        $this->googleConfiguration = $this->createMock(GoogleConfiguration::class);
        $this->googleHttpClient = $this->createMock(GoogleHttpClient::class);
        $this->googleOAuth2Client = new GoogleOAuth2Client($this->googleConfiguration, $this->googleHttpClient);
    }

    public function testItIsInitializable(): void
    {
        self::assertInstanceOf(GoogleOAuth2Client::class, $this->googleOAuth2Client);
    }

    public function testFetchUser(): void
    {
        $code = 'test_code';
        $redirectUri = 'test_redirect_uri';
        $oauthAccessResponse = new OAuth2AccessResponse(
            'test_access_token',
            1,
            'test_token_id',
            'user:read',
            'test_refresh_token',
        );
        $userInfoResponse = new UserInfoResponse(
            'test_id',
            'test_email',
            'test_name',
            'test_picture'
        );

        $this->googleHttpClient
            ->expects(self::once())
            ->method('oauthAccess')
            ->with($code, $redirectUri)
            ->willReturn($oauthAccessResponse)
        ;

        $this->googleHttpClient
            ->expects(self::once())
            ->method('fetchUser')
            ->with($oauthAccessResponse->accessToken)
            ->willReturn($userInfoResponse)
        ;

        $user = $this->googleOAuth2Client->fetchUser($code, $redirectUri);

        self::assertInstanceOf(OAuth2Identity::class, $user);
        self::assertSame(UserSocialAccountType::GOOGLE, $user->type);
        self::assertInstanceOf(AccessToken::class, $user->accessToken);
        self::assertSame(UserSocialAccountType::GOOGLE, $user->accessToken->type);
        self::assertSame($userInfoResponse->email, $user->accessToken->email);
        self::assertSame($oauthAccessResponse->accessToken, $user->accessToken->value);
        self::assertSame($oauthAccessResponse->expiresIn, $user->accessToken->expiresIn);
        self::assertSame($userInfoResponse->email, $user->email);
        self::assertSame($userInfoResponse->id, $user->externalUserId);
        self::assertSame(
            [
                'name' => $userInfoResponse->name,
                'picture' => $userInfoResponse->picture,
            ],
            $user->extraData
        );
    }

    public function testSupports(): void
    {
        self::assertTrue($this->googleOAuth2Client->supports(UserSocialAccountType::GOOGLE));
        self::assertFalse($this->googleOAuth2Client->supports(UserSocialAccountType::SLACK));
    }

    public function testCheckAuth(): void
    {
        self::assertTrue($this->googleOAuth2Client->checkAuth('test_access_token'));

        $this->googleHttpClient
            ->expects(self::once())
            ->method('oauthTest')
            ->with('test_incorrect_token')
            ->willThrowException(new GoogleApiException(''))
        ;

        self::assertFalse($this->googleOAuth2Client->checkAuth('test_incorrect_token'));
    }

    public function testGetAuthorizationUrl(): void
    {
        $googleConfiguration = new GoogleConfiguration(
            'client_id',
            'client_secret',
        );
        $googleOAuth2Client = new GoogleOAuth2Client($googleConfiguration, $this->googleHttpClient);
        $state = 'state';
        $redirectUri = 'http://localhost/callback';
        $encodedUri = urlencode($redirectUri);

        self::assertSame(
            "https://accounts.google.com/o/oauth2/v2/auth?redirect_uri={$encodedUri}&client_id=client_id&scope=openid+profile+email&state={$state}&response_type=code",
            $googleOAuth2Client->getAuthorizationUrl($state, $redirectUri)
        );
    }
}
