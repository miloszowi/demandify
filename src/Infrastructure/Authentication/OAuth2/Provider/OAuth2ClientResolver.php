<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Authentication\OAuth2\Provider;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;

class OAuth2ClientResolver
{
    public function __construct(
        /** @var OAuth2Client[] $oauth2Clients */
        private readonly iterable $oauth2Clients
    ) {}

    public function byType(UserSocialAccountType $type): OAuth2Client
    {
        foreach ($this->oauth2Clients as $client) {
            if ($client->supports($type)) {
                return $client;
            }
        }

        throw new \RuntimeException('OAuth2 client not found');
    }
}
