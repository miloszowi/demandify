<?php

declare(strict_types=1);

namespace Querify\Infrastructure\ExternalServices\Slack;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class SlackConfiguration
{
    public function __construct(
        #[Autowire(env: 'SLACK_APP_ID')]
        public string $appId,
        #[Autowire(env: 'SLACK_CLIENT_ID')]
        public string $clientId,
        #[
            \SensitiveParameter,
            Autowire(env: 'SLACK_CLIENT_SECRET')
        ]
        public string $clientSecret,
        #[
            \SensitiveParameter,
            Autowire(env: 'SLACK_SIGNING_SECRET')
        ]
        public string $signingSecret,
        #[
            \SensitiveParameter,
            Autowire(env: 'SLACK_OAUTH_BOT_TOKEN')
        ]
        public string $oauthBotToken,
        #[Autowire(env: 'SLACK_OAUTH_REDIRECT_URI')]
        public string $oauthRedirectUri,
        #[
            \SensitiveParameter,
            Autowire(env: 'SLACK_OAUTH_STATE_HASH_KEY')
        ]
        public string $oauthStateHashKey
    ) {}
}
