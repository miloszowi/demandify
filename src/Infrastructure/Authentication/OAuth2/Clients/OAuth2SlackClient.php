<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Authentication\OAuth2\Clients;

use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\Authentication\OAuth2\OAuth2Client;
use Querify\Infrastructure\Authentication\OAuth2\Response\OAuth2AccessResponse;
use Querify\Infrastructure\Authentication\OAuth2\Response\OAuth2ResourceOwner;
use Querify\Infrastructure\External\Slack\Http\Exception\SlackApiException;
use Querify\Infrastructure\External\Slack\Http\SlackHttpClient;
use Querify\Infrastructure\External\Slack\SlackConfiguration;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class OAuth2SlackClient implements OAuth2Client
{
    public function __construct(
        private readonly SlackHttpClient $slackHttpClient,
        private readonly SlackConfiguration $slackConfiguration
    ) {}

    public function supports(string $sessionId, string $state): bool
    {
        return $state === $this->getState($sessionId);
    }

    public function authorize(string $code): OAuth2AccessResponse
    {
        try {
            $oauthAccessResponse = $this->slackHttpClient->oauthAccess($code);
        } catch (SlackApiException) {
            throw new AuthenticationException('Failed to authorize.');
        }

        return new OAuth2AccessResponse(
            $oauthAccessResponse->authedUser->accessToken,
            $oauthAccessResponse->authedUser->id
        );
    }

    public function start(Request $request): RedirectResponse
    {
        $target = \sprintf(
            '%s?%s',
            'https://slack.com/oauth/v2/authorize',
            http_build_query([
                'redirect_uri' => $this->slackConfiguration->oauthRedirectUri,
                'client_id' => $this->slackConfiguration->clientId,
                'user_scope' => 'identify',
                'state' => $this->getState($request->getSession()->getId()),
            ])
        );

        return new RedirectResponse($target);
    }

    public function fetchUser(string $accessToken, string $externalUserId): OAuth2ResourceOwner
    {
        try {
            $user = $this->slackHttpClient->getUserInfo($externalUserId, $accessToken);
        } catch (SlackApiException) {
            throw new AuthenticationException('Failed to fetch user through provided accessToken.');
        }

        return new OAuth2ResourceOwner(
            $user->user->profile->email,
            $user->user->profile->realName,
            $user->user->id,
            (array) $user->user->profile
        );
    }

    public function getState(string $sessionId): string
    {
        return hash_hmac(
            'sha256',
            \sprintf('%s|%s', $sessionId, UserSocialAccountType::SLACK->value),
            $this->slackConfiguration->oauthStateHashKey,
        );
    }

    public function getLinkedSocialAccountType(): UserSocialAccountType
    {
        return UserSocialAccountType::SLACK;
    }
}
