<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Infrastructure\Authentication\OAuth2\Provider\Slack;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Authentication\AccessToken;
use Demandify\Infrastructure\Authentication\OAuth2\Provider\Slack\SlackOAuth2Client;
use Demandify\Infrastructure\Authentication\OAuth2\Response\OAuth2Identity;
use Demandify\Infrastructure\External\Slack\Http\Exception\SlackApiException;
use Demandify\Infrastructure\External\Slack\Http\Response\OAuth2Access\AuthedUser;
use Demandify\Infrastructure\External\Slack\Http\Response\OAuth2AccessResponse;
use Demandify\Infrastructure\External\Slack\Http\Response\UserInfo\Profile;
use Demandify\Infrastructure\External\Slack\Http\Response\UserInfo\User;
use Demandify\Infrastructure\External\Slack\Http\Response\UserInfoResponse;
use Demandify\Infrastructure\External\Slack\Http\SlackHttpClient;
use Demandify\Infrastructure\External\Slack\SlackConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @internal
 */
#[CoversClass(SlackOAuth2Client::class)]
final class SlackOAuth2ClientTest extends TestCase
{
    private SlackOAuth2Client $slackOAuth2Client;
    private MockObject|SlackHttpClient $slackHttpClient;
    private SlackConfiguration $slackConfiguration;

    protected function setUp(): void
    {
        $this->slackHttpClient = $this->createMock(SlackHttpClient::class);
        $this->slackConfiguration = new SlackConfiguration(
            'client_id',
            'client_secret',
            'signing_secret',
            'oauth_bot_token',
        );
        $this->slackOAuth2Client = new SlackOAuth2Client($this->slackHttpClient, $this->slackConfiguration);
    }

    public function testItIsInitializable(): void
    {
        self::assertInstanceOf(SlackOAuth2Client::class, $this->slackOAuth2Client);
    }

    public function testSupports(): void
    {
        self::assertTrue($this->slackOAuth2Client->supports(UserSocialAccountType::SLACK));
        self::assertFalse($this->slackOAuth2Client->supports(UserSocialAccountType::GOOGLE));
    }

    public function testFetchUser(): void
    {
        $code = 'test_code';
        $redirectUri = 'https://example.com/redirect';

        $oauthAccessResponse = new OAuth2AccessResponse(
            ok: true,
            authedUser: new AuthedUser(
                'U123',
                'scope',
                'xoxp-access-token',
                'token_type',
            )
        );

        $profile = new Profile('Engineer', '123-456-789', 'live:john.doe', 'John Doe', 'JohnDoe', 'JD', 'JD', ['field1' => 'value1'], 'Available', ':smile:', [':smile:'], 0, 'abc123', 'https://example.com/original.jpg', true, 'user@example.com', 'John', 'Doe', 'https://img.com/24.jpg', 'https://img.com/32.jpg', 'https://img.com/48.jpg', 'https://img.com/72.jpg', 'https://img.com/192.jpg', 'https://img.com/512.jpg', 'https://img.com/1024.jpg', 'available', 'T123');
        $user = new User('U123', 'T123', 'John Doe', false, 'red', 'Johnathan Doe', 'Europe/Warsaw', 'Central European Time', 3600, $profile, true, false, false, false, false, false, false, time(), true, true, 'everyone');

        $userInfoResponse = new UserInfoResponse(
            true,
            null,
            $user
        );

        $this->slackHttpClient
            ->expects(self::once())
            ->method('oauthAccess')
            ->with($code, $redirectUri)
            ->willReturn($oauthAccessResponse)
        ;

        $this->slackHttpClient
            ->expects(self::once())
            ->method('getUserInfo')
            ->with('U123', 'xoxp-access-token')
            ->willReturn($userInfoResponse)
        ;

        $identity = $this->slackOAuth2Client->fetchUser($code, $redirectUri);

        self::assertInstanceOf(OAuth2Identity::class, $identity);
        self::assertSame(UserSocialAccountType::SLACK, $identity->type);
        self::assertInstanceOf(AccessToken::class, $identity->accessToken);
        self::assertSame(UserSocialAccountType::SLACK, $identity->accessToken->type);
        self::assertSame('user@example.com', $identity->accessToken->email);
        self::assertSame('xoxp-access-token', $identity->accessToken->value);
        // todo fix this
        self::assertSame(PHP_INT_MAX, $identity->accessToken->expiresIn);
        self::assertSame('user@example.com', $identity->email);
        self::assertSame('U123', $identity->externalUserId);
        self::assertSame((array) $profile, $identity->extraData);
    }

    public function testFetchUserThrowsAuthenticationException(): void
    {
        $this->expectException(AuthenticationException::class);

        $this->slackHttpClient
            ->expects(self::once())
            ->method('oauthAccess')
            ->willThrowException(new SlackApiException('fail'))
        ;

        $this->slackOAuth2Client->fetchUser('bad_code', 'redirect_uri');
    }

    public function testCheckAuth(): void
    {
        self::assertTrue($this->slackOAuth2Client->checkAuth('valid_token'));

        $this->slackHttpClient
            ->expects(self::once())
            ->method('oauthTest')
            ->with('invalid_token')
            ->willThrowException(new SlackApiException('invalid'))
        ;

        self::assertFalse($this->slackOAuth2Client->checkAuth('invalid_token'));
    }

    public function testGetAuthorizationUrl(): void
    {
        $state = 'test_state';
        $redirectUri = 'https://example.com/callback';
        $encodedRedirectUri = urlencode($redirectUri);

        $expectedUrl = "https://slack.com/oauth/v2/authorize?redirect_uri={$encodedRedirectUri}&client_id=client_id&user_scope=team%3Aread%2Cidentify&state={$state}";

        self::assertSame($expectedUrl, $this->slackOAuth2Client->getAuthorizationUrl($state, $redirectUri));
    }
}
