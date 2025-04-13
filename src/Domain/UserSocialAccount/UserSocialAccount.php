<?php

declare(strict_types=1);

namespace Demandify\Domain\UserSocialAccount;

use Demandify\Domain\User\User;
use Doctrine\ORM\Mapping as ORM;

#[
    ORM\Entity(repositoryClass: UserSocialAccountRepository::class),
    ORM\Table('`user_social_account`'),
]
class UserSocialAccount
{
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isNotifiable = false;

    public function __construct(
        #[
            ORM\Id,
            ORM\ManyToOne(targetEntity: User::class, inversedBy: 'socialAccounts'),
            ORM\JoinColumn(name: 'user_uuid', referencedColumnName: 'uuid', nullable: false, onDelete: 'CASCADE'),
        ]
        public readonly User $user,
        #[
            ORM\Id,
            ORM\Column(length: 255)
        ]
        public readonly UserSocialAccountType $type,
        #[ORM\Column(length: 255)]
        public readonly string $externalId,
        /** @var array<string, string> */
        #[ORM\Column(type: 'json')]
        public readonly ?array $extraData = []
    ) {}

    public function isNotifiable(): bool
    {
        return $this->isNotifiable;
    }

    public function setNotifiable(bool $isNotifiable): void
    {
        $this->isNotifiable = $isNotifiable;
    }
}
