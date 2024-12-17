<?php

declare(strict_types=1);

namespace Querify\Domain\UserSocialAccount;

use Doctrine\ORM\Mapping as ORM;
use Querify\Domain\User\User;

#[
    ORM\Entity(repositoryClass: UserSocialAccountRepository::class),
    ORM\Table('`user_social_account`'),
]
readonly class UserSocialAccount
{
    public function __construct(
        #[
            ORM\Id,
            ORM\ManyToOne(targetEntity: User::class, inversedBy: 'socialAccounts'),
            ORM\JoinColumn(name: 'user_uuid', referencedColumnName: 'uuid', nullable: false, onDelete: 'CASCADE'),
        ]
        public User $user,
        #[
            ORM\Id,
            ORM\Column(length: 255)
        ]
        public UserSocialAccountType $type,
        #[ORM\Column(length: 255)]
        public string $externalId,
        /** @var array<string, string> */
        #[ORM\Column(type: 'json')]
        public ?array $extraData = []
    ) {}
}
