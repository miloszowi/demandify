<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Event\UserIdentityAuthorized;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;

readonly class UserIdentityAuthorized
{
    public function __construct(
        public UserSocialAccountType $type,
        public string $email,
        public string $externalUserId,
        /** @var mixed[] */
        public array $extraData
    ) {}
}
