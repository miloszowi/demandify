<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Authentication\OAuth2\Provider\Slack;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Authentication\AccessToken;
use Demandify\Infrastructure\Authentication\OAuth2\Provider\OAuth2Client;
use Demandify\Infrastructure\Authentication\OAuth2\Response\OAuth2Identity;
use Demandify\Infrastructure\External\Slack\Http\Exception\SlackApiException;
use Demandify\Infrastructure\External\Slack\Http\SlackHttpClient;
use Demandify\Infrastructure\External\Slack\SlackConfiguration;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SlackOAuth2Client implements OAuth2Client
{
    public function __construct(
        private readonly SlackHttpClient $slackHttpClient,
        private readonly SlackConfiguration $slackConfiguration
    ) {}

    public function getAuthorizationUrl(string $state, string $redirectUri): string
    {
        return \sprintf(
            '%s?%s',
            'https://slack.com/oauth/v2/authorize',
            http_build_query([
                'redirect_uri' => $redirectUri,
                'client_id' => $this->slackConfiguration->clientId,
                'user_scope' => 'team:read,identify',
                'state' => $state,
            ])
        );
    }

    public function fetchUser(string $code, string $redirectUri): OAuth2Identity
    {
        try {
            $oauthAccessResponse = $this->slackHttpClient->oauthAccess($code, $redirectUri);
            $user = $this->slackHttpClient->getUserInfo($oauthAccessResponse->authedUser->id, $oauthAccessResponse->authedUser->accessToken);
        } catch (SlackApiException) {
            throw new AuthenticationException('Failed to fetch user through provided accessToken.');
        }

        return new OAuth2Identity(
            UserSocialAccountType::SLACK,
            new AccessToken(
                UserSocialAccountType::SLACK,
                $user->user->profile->email,
                $oauthAccessResponse->authedUser->accessToken,
                PHP_INT_MAX // TODO: CHANGE THIS
            ),
            $user->user->profile->email,
            $user->user->id,
            (array) $user->user->profile
        );
    }

    public function supports(UserSocialAccountType $type): bool
    {
        return $type->isEqualTo(UserSocialAccountType::SLACK);
    }

    public function checkAuth(string $accessToken): bool
    {
        try {
            $this->slackHttpClient->oauthTest($accessToken);

            return true;
        } catch (SlackApiException) {
            return false;
        }
    }
}
