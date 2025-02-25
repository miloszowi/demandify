<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Authentication\OAuth2;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Authentication\OAuth2\Response\OAuth2AccessResponse;
use Demandify\Infrastructure\Authentication\OAuth2\Response\OAuth2ResourceOwner;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

interface OAuth2Client
{
    public function supports(string $sessionId, string $state): bool;

    public function start(Request $request): RedirectResponse;

    public function authorize(string $code): OAuth2AccessResponse;

    public function fetchUser(string $accessToken, string $externalUserId): OAuth2ResourceOwner;

    public function getState(string $sessionId): string;

    public function getLinkedSocialAccountType(): UserSocialAccountType;
}
