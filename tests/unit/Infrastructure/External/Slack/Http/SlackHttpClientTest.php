<?php

declare(strict_types=1);

namespace Querify\Tests\Unit\Infrastructure\External\Slack\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Querify\Infrastructure\External\Slack\Http\Exception\SlackApiException;
use Querify\Infrastructure\External\Slack\Http\Response\Oauth2AccessResponse;
use Querify\Infrastructure\External\Slack\Http\Response\UserInfoResponse;
use Querify\Infrastructure\External\Slack\Http\SlackHttpClient;
use Querify\Infrastructure\External\Slack\SlackConfiguration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 */
#[CoversClass(SlackHttpClient::class)]
final class SlackHttpClientTest extends TestCase
{
    private HttpClientInterface|MockObject $slackApiHttpClient;
    private MockObject|SerializerInterface $serializer;
    private SlackHttpClient $slackHttpClient;

    protected function setUp(): void
    {
        $this->slackApiHttpClient = $this->createMock(HttpClientInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);

        $slackConfiguration = new SlackConfiguration(
            'some_app_id',
            'some_client_id',
            'some_client_secret',
            'some_signing_secret',
            'some_oauth_bot_token',
            'https://redirect.uri',
            'some_oauth_state_hash_key'
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
            ->with(Request::METHOD_GET, '/api/oauth.v2.access', self::anything())
            ->willReturn($response)
        ;

        $oauthResponse = new Oauth2AccessResponse(
            ok: true,
            error: null,
            accessToken: 'some_access_token'
        );

        $this->serializer->method('deserialize')
            ->with($responseContent, Oauth2AccessResponse::class, 'json')
            ->willReturn($oauthResponse)
        ;

        $result = $this->slackHttpClient->oauthAccess('some_code');
        self::assertSame($oauthResponse, $result);
    }

    public function testItShouldThrowExceptionWhenOauthAccessFails(): void
    {
        $this->expectException(SlackApiException::class);

        $responseContent = '{"ok": false, "error": "invalid_grant"}';
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn($responseContent);

        $this->slackApiHttpClient->method('request')
            ->with(Request::METHOD_GET, '/api/oauth.v2.access', self::anything())
            ->willReturn($response)
        ;

        $this->serializer->method('deserialize')
            ->with($responseContent, Oauth2AccessResponse::class, 'json')
            ->willReturn(new Oauth2AccessResponse(false, 'invalid_grant'))
        ;

        $this->slackHttpClient->oauthAccess('some_code');
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
