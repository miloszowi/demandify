<?php

declare(strict_types=1);

namespace Querify\Infrastructure\ExternalServices\OAuth;

final readonly class OAuthManager
{
    public function __construct(
        /** @var OAuthHandler[] $oauthHandlers */
        private iterable $oauthHandlers
    ) {}

    public function handle(string $code, string $state, string $userIdentifier): void
    {
        foreach ($this->oauthHandlers as $oauthHandler) {
            if ($oauthHandler->supports($state, $userIdentifier)) {
                $oauthHandler->handle($code, $userIdentifier);
            }
        }
    }

    /**
     * @return OAuthHandler[]
     */
    public function getOAuthHandlers(): iterable
    {
        return $this->oauthHandlers;
    }
}
