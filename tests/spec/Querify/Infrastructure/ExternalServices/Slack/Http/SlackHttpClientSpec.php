<?php

declare(strict_types=1);

namespace spec\Querify\Infrastructure\ExternalServices\Slack\Http;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Querify\Infrastructure\ExternalServices\Slack\Http\Exception\SlackApiException;
use Querify\Infrastructure\ExternalServices\Slack\Http\Response\Oauth2AccessResponse;
use Querify\Infrastructure\ExternalServices\Slack\Http\Response\UserInfoResponse;
use Querify\Infrastructure\ExternalServices\Slack\Http\SlackHttpClient;
use Querify\Infrastructure\ExternalServices\Slack\SlackConfiguration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SlackHttpClientSpec extends ObjectBehavior
{
    public function let(
        HttpClientInterface $slackApiHttpClient,
        SerializerInterface $serializer
    ): void {
        $slackConfiguration = new SlackConfiguration(
            'some_app_id',
            'some_client_id',
            'some_client_secret',
            'some_signing_secret',
            'some_oauth_bot_token',
            'https://redirect.uri',
            'some_oauth_state_hash_key'
        );

        $this->beConstructedWith($slackApiHttpClient, $slackConfiguration, $serializer);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(SlackHttpClient::class);
    }

    public function it_should_get_oauth_access(
        HttpClientInterface $slackApiHttpClient,
        SerializerInterface $serializer,
        ResponseInterface $response,
    ): void {
        $responseContent = '{"ok": true, "access_token": "some_access_token"}';

        $response->getContent()->willReturn($responseContent);

        $slackApiHttpClient->request(
            Request::METHOD_GET,
            '/api/oauth.v2.access',
            Argument::any()
        )->willReturn($response);

        $oauthResponse = new Oauth2AccessResponse(
            ok: true,
            error: null,
            accessToken: 'some_access_token'
        );
        $serializer->deserialize($responseContent, Oauth2AccessResponse::class, 'json')
            ->willReturn($oauthResponse)
        ;

        $this->oauthAccess('some_code')->shouldReturn($oauthResponse);
    }

    public function it_should_throw_exception_when_oauth_access_fails(
        HttpClientInterface $slackApiHttpClient,
        SerializerInterface $serializer,
        ResponseInterface $response
    ): void {
        $responseContent = '{"ok": false, "error": "invalid_grant"}';

        $response->getContent()->willReturn($responseContent);

        $slackApiHttpClient->request(
            Request::METHOD_GET,
            '/api/oauth.v2.access',
            Argument::any()
        )->willReturn($response);

        $serializer->deserialize($responseContent, Oauth2AccessResponse::class, 'json')
            ->willReturn(new Oauth2AccessResponse(false, 'invalid_grant', null, null, null, null, null, null, null, null))
        ;

        $this->shouldThrow(SlackApiException::class)->during('oauthAccess', ['some_code']);
    }

    public function it_should_get_user_info(
        HttpClientInterface $slackApiHttpClient,
        SerializerInterface $serializer,
        ResponseInterface $response
    ): void {
        $responseContent = '{"ok": true, "user": {"id": "U12345", "name": "John Doe"}}';

        $response->getContent()->willReturn($responseContent);

        $slackApiHttpClient->request(
            Request::METHOD_GET,
            '/api/users.info',
            Argument::any()
        )->willReturn($response);

        $userInfoResponse = new UserInfoResponse(true, null, null);

        $serializer->deserialize($responseContent, UserInfoResponse::class, 'json')
            ->willReturn($userInfoResponse)
        ;

        $this->getUserInfo('U12345', 'some_token')->shouldReturn($userInfoResponse);
    }

    public function it_should_throw_exception_when_get_user_info_fails(
        HttpClientInterface $slackApiHttpClient,
        SerializerInterface $serializer,
        ResponseInterface $response
    ): void {
        $responseContent = '{"ok": false, "error": "user_not_found"}';

        $response->getContent()->willReturn($responseContent);

        $slackApiHttpClient->request(
            Request::METHOD_GET,
            '/api/users.info',
            Argument::any()
        )->willReturn($response);

        $serializer->deserialize($responseContent, UserInfoResponse::class, 'json')
            ->willReturn(new UserInfoResponse(false, 'user_not_found', null))
        ;

        $this->shouldThrow(SlackApiException::class)->during('getUserInfo', ['U12345', 'some_token']);
    }
}
