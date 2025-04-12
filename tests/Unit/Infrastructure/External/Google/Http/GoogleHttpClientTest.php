<?php

declare(strict_types=1);

namespace Demandify\Tests\Unit\Infrastructure\External\Google\Http;

use Demandify\Infrastructure\External\Google\GoogleConfiguration;
use Demandify\Infrastructure\External\Google\Http\Exception\GoogleApiException;
use Demandify\Infrastructure\External\Google\Http\GoogleHttpClient;
use Demandify\Infrastructure\External\Google\Http\Response\OAuth2AccessResponse;
use Demandify\Infrastructure\External\Google\Http\Response\UserInfoResponse;
use Demandify\Infrastructure\External\Http\LoggingAwareHttpClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 */
#[CoversClass(GoogleHttpClient::class)]
final class GoogleHttpClientTest extends TestCase
{
    private LoggingAwareHttpClient|MockObject $googleApiHttpClient;
    private MockObject|SerializerInterface $serializer;
    private GoogleHttpClient $googleHttpClient;

    protected function setUp(): void
    {
        $this->googleApiHttpClient = $this->createMock(LoggingAwareHttpClient::class);
        $this->serializer = $this->createMock(SerializerInterface::class);

        $googleConfiguration = new GoogleConfiguration(
            'client_id',
            'client_secret',
        );

        $this->googleHttpClient = new GoogleHttpClient($this->googleApiHttpClient, $googleConfiguration, $this->serializer);
    }

    public function testItIsInitializable(): void
    {
        self::assertInstanceOf(GoogleHttpClient::class, $this->googleHttpClient);
    }

    public function testItShouldGetOauthAccess(): void
    {
        $responseContent = '{"access_token": "some_access_token", "expires_in": 1337, "id_token": "token, "scope": "scope", "token_type": "token_type"}';
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn($responseContent);
        $response->method('getStatusCode')->willReturn(Response::HTTP_OK);

        $this->googleApiHttpClient->method('request')
            ->with(Request::METHOD_POST, 'https://oauth2.googleapis.com/token', self::anything())
            ->willReturn($response)
        ;

        $oauthResponse = new OAuth2AccessResponse(
            'some_access_token',
            1337,
            'token_id',
            'scope',
            'token_type',
        );

        $this->serializer->method('deserialize')
            ->with($responseContent, OAuth2AccessResponse::class, 'json')
            ->willReturn($oauthResponse)
        ;

        $response = $this->googleHttpClient->oauthAccess('some_code', 'some_redirect_uri');
        self::assertSame($oauthResponse, $response);
    }

    public function testItShouldThrowExceptionWhenOauthAccessFails(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(Response::HTTP_UNAUTHORIZED);

        $this->googleApiHttpClient->method('request')
            ->with(Request::METHOD_POST, 'https://oauth2.googleapis.com/token', self::anything())
            ->willReturn($response)
        ;

        $this->expectException(GoogleApiException::class);
        $this->expectExceptionMessage('Failed to fetch access token');

        $this->googleHttpClient->oauthAccess('some_code', 'some_redirect_uri');
    }

    public function testItShouldFetchUser(): void
    {
        $responseContent = '{"id": "some_id", "email": "some_email", "name": "some_name", "picture": "some_picture"}';
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn($responseContent);
        $response->method('getStatusCode')->willReturn(Response::HTTP_OK);

        $this->googleApiHttpClient->method('request')
            ->with(Request::METHOD_GET, '/oauth2/v1/userinfo', self::anything())
            ->willReturn($response)
        ;

        $userInfoResponse = new UserInfoResponse(
            'some_id',
            'some_email',
            'some_name',
            'some_picture',
        );

        $this->serializer->method('deserialize')
            ->with($responseContent, UserInfoResponse::class, 'json')
            ->willReturn($userInfoResponse)
        ;

        $response = $this->googleHttpClient->fetchUser('some_access_token');
        self::assertSame($userInfoResponse, $response);
    }

    public function testItShouldThrowExceptionWhenFetchUserFails(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(Response::HTTP_UNAUTHORIZED);

        $this->googleApiHttpClient->method('request')
            ->with(Request::METHOD_GET, '/oauth2/v1/userinfo', self::anything())
            ->willReturn($response)
        ;

        $this->expectException(GoogleApiException::class);
        $this->expectExceptionMessage('Failed to fetch user info');

        $this->googleHttpClient->fetchUser('some_access_token');
    }
}
