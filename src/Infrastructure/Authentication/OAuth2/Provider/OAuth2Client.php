<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Authentication\OAuth2\Provider;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Authentication\OAuth2\Response\OAuth2Identity;

interface OAuth2Client
{
    public function fetchUser(string $code, string $redirectUri): OAuth2Identity;

    public function supports(UserSocialAccountType $type): bool;

    public function checkAuth(string $accessToken): bool;

    public function getAuthorizationUrl(string $state, string $redirectUri): string;
}
