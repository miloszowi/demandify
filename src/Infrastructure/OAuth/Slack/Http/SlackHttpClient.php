<?php

declare(strict_types=1);

namespace Querify\Infrastructure\OAuth\Slack\Http;

use Querify\Infrastructure\OAuth\Slack\Http\Exception\SlackApiException;
use Querify\Infrastructure\OAuth\Slack\Http\Response\OauthV2AccessResponse;
use Querify\Infrastructure\OAuth\Slack\SlackConfiguration;
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

    public function oauthAccess(string $code): OauthV2AccessResponse
    {
        $response = $this->slackApiHttpClient->request(
            Request::METHOD_GET,
            '/api/oauth.v2.access',
            [
                'query' => [
                    'code' => $code,
                    'redirect_uri' => $this->slackConfiguration->oauthRedirectUri,
                ],
                'auth_basic' => [
                    $this->slackConfiguration->clientId,
                    $this->slackConfiguration->clientSecret,
                ],
            ]
        );

        $response = $this->serializer->deserialize(
            $response->getContent(),
            OauthV2AccessResponse::class,
            JsonEncoder::FORMAT
        );

        if (false === $response->ok) {
            throw SlackApiException::fromError($response->error);
        }

        return $response;
    }
}
