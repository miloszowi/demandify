<?php

declare(strict_types=1);

namespace Demandify\Application\Command\LinkSocialAccountToUser;

use Demandify\Domain\UserSocialAccount\UserSocialAccountType;
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
