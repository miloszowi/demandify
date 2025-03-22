<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Authentication;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;

readonly class AccessToken
{
    public function __construct(
        public UserSocialAccountType $type,
        public string $email,
        public string $value,
        public int $expiresIn,
    ) {}
}
