<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\External\Slack\Http;

use Demandify\Infrastructure\External\Http\LoggingAwareHttpClient;
use Demandify\Infrastructure\External\Slack\Http\Exception\SlackApiException;
use Demandify\Infrastructure\External\Slack\Http\Response\OAuth2AccessResponse;
use Demandify\Infrastructure\External\Slack\Http\Response\UserInfoResponse;
use Demandify\Infrastructure\External\Slack\SlackConfiguration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class SlackHttpClient
{
    public function __construct(
        private readonly LoggingAwareHttpClient $slackApiHttpClient,
        private readonly SlackConfiguration $slackConfiguration,
        private readonly SerializerInterface $serializer
    ) {}

    public function oauthAccess(string $code, string $redirectUri): OAuth2AccessResponse
    {
        $response = $this->slackApiHttpClient->request(
            Request::METHOD_POST,
            '/api/oauth.v2.access',
            [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'redirect_uri' => $redirectUri,
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                ],
                'auth_basic' => [
                    $this->slackConfiguration->clientId,
                    $this->slackConfiguration->clientSecret,
                ],
            ]
        );

        $response = $this->serializer->deserialize(
            $response->getContent(),
            OAuth2AccessResponse::class,
            JsonEncoder::FORMAT
        );

        if (false === $response->ok) {
            throw SlackApiException::fromError((string) $response->error);
        }

        return $response;
    }

    public function getUserInfo(string $slackUserId, string $token): UserInfoResponse
    {
        $response = $this->slackApiHttpClient->request(
            Request::METHOD_GET,
            '/api/users.info',
            [
                'auth_bearer' => $token,
                'query' => [
                    'user' => $slackUserId,
                ],
            ]
        );

        $response = $this->serializer->deserialize(
            $response->getContent(),
            UserInfoResponse::class,
            JsonEncoder::FORMAT
        );

        if (false === $response->ok) {
            throw SlackApiException::fromError((string) $response->error);
        }

        return $response;
    }

    public function oauthTest(string $accessToken): void
    {
        $response = $this->slackApiHttpClient->request(
            Request::METHOD_POST,
            '/api/auth.test',
            [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Bearer '.$accessToken,
                ],
            ]
        );

        if (false === $response->toArray()['ok']) {
            // todo logging
            throw SlackApiException::fromError((string) $response->toArray()['error']);
        }
    }
}
