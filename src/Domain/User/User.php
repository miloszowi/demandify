<?php

declare(strict_types=1);

namespace Querify\Domain\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Domain\UserSocialAccount\UserSocialAccountType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[
    ORM\Entity(repositoryClass: UserRepository::class),
    ORM\Table(name: '`user`')
]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, EquatableInterface
{
    /**
     * @readonly impossible to specify "readonly" attribute natively due
     *           to a Doctrine feature/bug https://github.com/doctrine/orm/issues/9863
     */
    #[
        ORM\Id,
        ORM\Column(name: 'uuid', type: 'uuid', unique: true, nullable: false)
    ]
    public UuidInterface $uuid;

    #[ORM\Column(type: 'datetimetz_immutable')]
    public readonly \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetimetz_immutable')]
    public readonly \DateTimeImmutable $updatedAt;

    /**
     * @var UserRole[] $roles
     */
    #[ORM\Column(type: 'json')]
    private array $roles;

    #[ORM\OneToMany(targetEntity: UserSocialAccount::class, mappedBy: 'user', cascade: ['persist', 'remove'], fetch: 'EAGER')]
    private Collection $socialAccounts;

    public function __construct(
        #[ORM\Embedded(class: Email::class, columnPrefix: false)]
        public readonly Email $email,
        #[ORM\Column(length: 255)]
        public readonly string $name,
    ) {
        $this->uuid = Uuid::uuid4();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $this->roles = [UserRole::ROLE_USER];
        $this->socialAccounts = new ArrayCollection();
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function grantPrivilege(UserRole $role): void
    {
        if (array_any($this->roles, static fn ($assignedRole) => $role->value === $assignedRole->value)) {
            return;
        }

        $this->roles[] = $role->value;
    }

    public function hasPrivilege(UserRole $role): bool
    {
        return array_any($this->roles, static fn ($assignedRole) => $role->value === $assignedRole->value);
    }

    public function getSocialAccount(UserSocialAccountType $userSocialAccountType): ?UserSocialAccount
    {
        return array_find($this->getSocialAccounts()->toArray(), static fn ($socialAccount) => $userSocialAccountType->isEqualTo($socialAccount->type));
    }

    public function hasSocialAccountLinked(UserSocialAccountType $userSocialAccountType): bool
    {
        return array_any($this->getSocialAccounts()->toArray(), static fn ($socialAccount) => $userSocialAccountType->isEqualTo($socialAccount->type));
    }

    public function getSocialAccounts(): Collection
    {
        return !empty($this->socialAccounts) ? $this->socialAccounts : new ArrayCollection();
    }

    public function linkSocialAccount(UserSocialAccount $userSocialAccount): void
    {
        $this->socialAccounts->add($userSocialAccount);
    }

    /**
     * @return string[]|UserRole[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * No credentials are stored as logging in is only available through OAuth2.
     */
    public function eraseCredentials(): void {}

    public function isEqualTo(UserInterface $user): bool
    {
        return $user->getUserIdentifier() === $this->getUserIdentifier();
    }
}
