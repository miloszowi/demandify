<?php

declare(strict_types=1);

namespace Querify\Domain\UserSocialAccount;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[
    ORM\Entity(repositoryClass: UserSocialAccountRepository::class),
    ORM\Table('`user_social_account`'),
]
final readonly class UserSocialAccount
{
    public function __construct(
        #[
            ORM\Id,
            ORM\Column(type: 'uuid')
        ]
        public UuidInterface $userUuid,
        #[
            ORM\Id,
            ORM\Column(length: 255)
        ]
        public UserSocialAccountType $type,
        #[ORM\Column(length: 255)]
        public string $externalId,
        /** @var array<string, string> */
        #[ORM\Column(type: 'json')]
        public ?array $extraData
    ) {}
}
