<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Authentication\OAuth2\Provider\Google;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Authentication\AccessToken;
use Demandify\Infrastructure\Authentication\OAuth2\Provider\OAuth2Client;
use Demandify\Infrastructure\Authentication\OAuth2\Response\OAuth2Identity;
use Demandify\Infrastructure\External\Google\GoogleConfiguration;
use Demandify\Infrastructure\External\Google\Http\Exception\GoogleApiException;
use Demandify\Infrastructure\External\Google\Http\GoogleHttpClient;

class GoogleOAuth2Client implements OAuth2Client
{
    public function __construct(
        private readonly GoogleConfiguration $googleConfiguration,
        private readonly GoogleHttpClient $googleHttpClient,
    ) {}

    public function fetchUser(string $code, string $redirectUri): OAuth2Identity
    {
        $oauthAccess = $this->googleHttpClient->oauthAccess($code, $redirectUri);
        $user = $this->googleHttpClient->fetchUser($oauthAccess->accessToken);

        return new OAuth2Identity(
            UserSocialAccountType::GOOGLE,
            new AccessToken(
                UserSocialAccountType::GOOGLE,
                $user->email,
                $oauthAccess->accessToken,
                $oauthAccess->expiresIn
            ),
            $user->email,
            $user->id,
            [
                'name' => $user->name,
                'picture' => $user->picture,
            ]
        );
    }

    public function supports(UserSocialAccountType $type): bool
    {
        return $type->isEqualTo(UserSocialAccountType::GOOGLE);
    }

    public function checkAuth(string $accessToken): bool
    {
        try {
            $this->googleHttpClient->oauthTest($accessToken);

            return true;
        } catch (GoogleApiException) {
            return false;
        }
    }

    public function getAuthorizationUrl(string $state, string $redirectUri): string
    {
        return \sprintf(
            '%s?%s',
            'https://accounts.google.com/o/oauth2/v2/auth',
            http_build_query([
                'redirect_uri' => $redirectUri,
                'client_id' => $this->googleConfiguration->clientId,
                'scope' => 'openid profile email',
                'state' => $state,
                'response_type' => 'code',
            ])
        );
    }
}
