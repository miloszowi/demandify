<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Authentication\OAuth2\Response;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
use Demandify\Infrastructure\Authentication\AccessToken;

final readonly class OAuth2Identity
{
    public function __construct(
        public UserSocialAccountType $type,
        public AccessToken $accessToken,
        public string $email,
        public string $externalUserId,
        /** @var array<mixed> */
        public array $extraData = []
    ) {}
}
