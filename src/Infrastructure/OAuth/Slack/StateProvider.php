<?php

declare(strict_types=1);

namespace Querify\Infrastructure\OAuth\Slack;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class StateProvider
{
    public function __construct(
        #[Autowire(env: 'SLACK_OAUTH_STATE_HASH_KEY')]
        private readonly string $stateHashKey,
    ) {}

    public function provideForUser(string $userIdentifier): string
    {
        return hash_hmac(
            'sha256',
            $userIdentifier,
            $this->stateHashKey,
        );
    }

    public function isValidForUser(string $userIdentifier, string $state): bool
    {
        return $state === $this->provideForUser($userIdentifier);
    }
}
