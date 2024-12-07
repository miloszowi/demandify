<?php

declare(strict_types=1);

namespace Querify\Application\Command\LinkSocialAccountToUser;

use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
final readonly class LinkSocialAccountToUser
{
    public function __construct(
        public string $userEmail,
        public UserSocialAccountType $type,
        public string $externalId,
        /** @var array<string, string> */
        public ?array $extraData = []
    ) {}
}
