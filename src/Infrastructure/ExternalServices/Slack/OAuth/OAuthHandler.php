<?php

declare(strict_types=1);

namespace Querify\Infrastructure\ExternalServices\Slack\OAuth;

use Querify\Application\Command\LinkSocialAccountToUser\LinkSocialAccountToUser;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Querify\Infrastructure\ExternalServices\OAuth\OAuthHandler as OAuthHandlerInterface;
use Querify\Infrastructure\ExternalServices\Slack\Http\SlackHttpClient;
use Querify\Infrastructure\ExternalServices\Slack\SlackConfiguration;
use Symfony\Component\Messenger\MessageBusInterface;

class OAuthHandler implements OAuthHandlerInterface
{
    public function __construct(
        private readonly SlackConfiguration $slackConfiguration,
        private readonly SlackHttpClient $slackHttpClient,
        private readonly MessageBusInterface $messageBus,
    ) {}

    public function supports(string $state, string $userIdentifier): bool
    {
        return $state === $this->provideState($userIdentifier);
    }

    public function handle(string $code, string $userIdentifier): void
    {
        $oauthResponse = $this->slackHttpClient->oauthAccess($code);
        $user = $this->slackHttpClient->getUserInfo(
            $oauthResponse->authedUser->id,
            $oauthResponse->authedUser->accessToken
        );

        $this->messageBus->dispatch(
            new LinkSocialAccountToUser(
                $userIdentifier,
                UserSocialAccountType::SLACK,
                $user->user->id,
                (array) $user->user,
            )
        );
    }

    public function provideState(string $userIdentifier): string
    {
        return hash_hmac(
            'sha256',
            \sprintf('%s|slack', $userIdentifier),
            $this->slackConfiguration->oauthStateHashKey,
        );
    }

    public function getOauthUrl(): string
    {
        return \sprintf(
            '%s?%s',
            'https://slack.com/oauth/v2/authorize',
            http_build_query([
                'redirect_uri' => $this->slackConfiguration->oauthRedirectUri,
                'client_id' => $this->slackConfiguration->clientId,
                'user_scope' => 'identify',
            ])
        );
    }

    public function getName(): string
    {
        return UserSocialAccountType::SLACK->value;
    }
}
