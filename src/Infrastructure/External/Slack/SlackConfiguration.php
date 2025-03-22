<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\External\Slack;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class SlackConfiguration
{
    public function __construct(
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
    ) {}
}
