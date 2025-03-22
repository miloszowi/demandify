<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\External\Google\Http;

use Demandify\Infrastructure\External\Google\GoogleConfiguration;
use Demandify\Infrastructure\External\Google\Http\Exception\GoogleApiException;
use Demandify\Infrastructure\External\Google\Http\Response\OAuth2AccessResponse;
use Demandify\Infrastructure\External\Google\Http\Response\UserInfoResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GoogleHttpClient
{
    public function __construct(
        private readonly HttpClientInterface $googleApiHttpClient,
        private readonly GoogleConfiguration $googleConfiguration,
        private readonly SerializerInterface $serializer
    ) {}

    public function oauthAccess(string $code, string $redirectUri): OAuth2AccessResponse
    {
        $response = $this->googleApiHttpClient->request(
            Request::METHOD_POST,
            'https://oauth2.googleapis.com/token',
            [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'redirect_uri' => $redirectUri,
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'client_id' => $this->googleConfiguration->clientId,
                    'client_secret' => $this->googleConfiguration->clientSecret,
                ],
            ]
        );

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            // todo logging
            throw GoogleApiException::fromError('Failed to fetch access token');
        }

        return $this->serializer->deserialize(
            $response->getContent(),
            OAuth2AccessResponse::class,
            JsonEncoder::FORMAT
        );
    }

    public function fetchUser(string $accessToken): UserInfoResponse
    {
        $response = $this->googleApiHttpClient->request(
            Request::METHOD_GET,
            '/oauth2/v1/userinfo',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                ],
            ]
        );

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            // todo logging
            throw GoogleApiException::fromError('Failed to fetch user info');
        }

        return $this->serializer->deserialize(
            $response->getContent(),
            UserInfoResponse::class,
            JsonEncoder::FORMAT
        );
    }

    public function oauthTest(string $accessToken): void
    {
        $response = $this->googleApiHttpClient->request(
            Request::METHOD_GET,
            '/oauth2/v1/tokeninfo',
            [
                'query' => [
                    'access_token' => $accessToken,
                ],
            ]
        );

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            // todo logging
            throw GoogleApiException::fromError('Failed to check access token');
        }
    }
}
