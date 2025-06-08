<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Infrastructure\External\Slack\Http;

use Demandify\Infrastructure\External\Http\LoggingAwareHttpClient;
use Demandify\Infrastructure\External\Slack\Http\Exception\SlackApiException;
use Demandify\Infrastructure\External\Slack\Http\Response\OAuth2AccessResponse;
use Demandify\Infrastructure\External\Slack\Http\Response\UserInfoResponse;
use Demandify\Infrastructure\External\Slack\Http\SlackHttpClient;
use Demandify\Infrastructure\External\Slack\SlackConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 */
#[CoversClass(SlackHttpClient::class)]
final class SlackHttpClientTest extends TestCase
{
    private LoggingAwareHttpClient|MockObject $slackApiHttpClient;
    private MockObject|SerializerInterface $serializer;
    private SlackHttpClient $slackHttpClient;

    protected function setUp(): void
    {
        $this->slackApiHttpClient = $this->createMock(LoggingAwareHttpClient::class);
        $this->serializer = $this->createMock(SerializerInterface::class);

        $slackConfiguration = new SlackConfiguration(
            'client_id',
            'client_secret',
            'signing_secret',
            'oauth_bot_token',
        );

        $this->slackHttpClient = new SlackHttpClient($this->slackApiHttpClient, $slackConfiguration, $this->serializer);
    }

    public function testItIsInitializable(): void
    {
        self::assertInstanceOf(SlackHttpClient::class, $this->slackHttpClient);
    }

    public function testItShouldGetOauthAccess(): void
    {
        $responseContent = '{"ok": true, "access_token": "some_access_token"}';
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn($responseContent);

        $this->slackApiHttpClient->method('request')
            ->with(Request::METHOD_POST, '/api/oauth.v2.access', self::anything())
            ->willReturn($response)
        ;

        $oauthResponse = new OAuth2AccessResponse(
            ok: true,
            error: null,
            accessToken: 'some_access_token'
        );

        $this->serializer->method('deserialize')
            ->with($responseContent, OAuth2AccessResponse::class, 'json')
            ->willReturn($oauthResponse)
        ;

        $result = $this->slackHttpClient->oauthAccess('some_code', 'https://localhost/oauth/slack/check');
        self::assertSame($oauthResponse, $result);
    }

    public function testItShouldThrowExceptionWhenOauthAccessFails(): void
    {
        $this->expectException(SlackApiException::class);

        $responseContent = '{"ok": false, "error": "invalid_grant"}';
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn($responseContent);

        $this->slackApiHttpClient->method('request')
            ->with(Request::METHOD_POST, '/api/oauth.v2.access', self::anything())
            ->willReturn($response)
        ;

        $this->serializer->method('deserialize')
            ->with($responseContent, OAuth2AccessResponse::class, 'json')
            ->willReturn(new OAuth2AccessResponse(false, 'invalid_grant'))
        ;

        $this->slackHttpClient->oauthAccess('some_code', 'https://localhost/oauth/slack/check');
    }

    public function testItShouldGetUserInfo(): void
    {
        $responseContent = '{"ok": true, "user": {"id": "U12345", "name": "John Doe"}}';
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn($responseContent);

        $this->slackApiHttpClient->method('request')
            ->with(Request::METHOD_GET, '/api/users.info', self::anything())
            ->willReturn($response)
        ;

        $userInfoResponse = new UserInfoResponse(true, null, null);

        $this->serializer->method('deserialize')
            ->with($responseContent, UserInfoResponse::class, 'json')
            ->willReturn($userInfoResponse)
        ;

        $result = $this->slackHttpClient->getUserInfo('U12345', 'some_token');
        self::assertSame($userInfoResponse, $result);
    }

    public function testItShouldThrowExceptionWhenGetUserInfoFails(): void
    {
        $this->expectException(SlackApiException::class);

        $responseContent = '{"ok": false, "error": "user_not_found"}';
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn($responseContent);

        $this->slackApiHttpClient->method('request')
            ->with(Request::METHOD_GET, '/api/users.info', self::anything())
            ->willReturn($response)
        ;

        $this->serializer->method('deserialize')
            ->with($responseContent, UserInfoResponse::class, 'json')
            ->willReturn(new UserInfoResponse(false, 'user_not_found', null))
        ;

        $this->slackHttpClient->getUserInfo('U12345', 'some_token');
    }
}
