<?php

declare(strict_types=1);

namespace Querify\Application\Command\LinkSocialAccountToUser;

use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
readonly class LinkSocialAccountToUser
{
    public function __construct(
        public string $userEmail,
        public string $name,
        public UserSocialAccountType $type,
        public string $externalId,
        /** @var mixed[] */
        public ?array $extraData = []
    ) {}
}
