<?php

declare(strict_types=1);

namespace Querify\Infrastructure\External\Slack\Http;

use Querify\Infrastructure\External\Slack\Http\Exception\SlackApiException;
use Querify\Infrastructure\External\Slack\Http\Response\Oauth2AccessResponse;
use Querify\Infrastructure\External\Slack\Http\Response\UserInfoResponse;
use Querify\Infrastructure\External\Slack\SlackConfiguration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SlackHttpClient
{
    public function __construct(
        private readonly HttpClientInterface $slackApiHttpClient,
        private readonly SlackConfiguration $slackConfiguration,
        private readonly SerializerInterface $serializer
    ) {}

    public function oauthAccess(string $code): Oauth2AccessResponse
    {
        $response = $this->slackApiHttpClient->request(
            Request::METHOD_GET,
            '/api/oauth.v2.access',
            [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'query' => [
                    'code' => $code,
                    'redirect_uri' => $this->slackConfiguration->oauthRedirectUri,
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
            Oauth2AccessResponse::class,
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
}
