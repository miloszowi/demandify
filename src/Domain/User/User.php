<?php

declare(strict_types=1);

namespace Querify\Domain\User;

use Doctrine\ORM\Mapping as ORM;
use Querify\Domain\UserRole;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[
    ORM\Entity(repositoryClass: \Querify\Infrastructure\Repository\UserRepository::class),
    ORM\Table(name: '`user`')
]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    #[
        ORM\Id,
        ORM\Column(name: 'uuid', type: 'uuid', unique: true, nullable: false)
    ]
    public readonly UuidInterface $uuid;

    #[ORM\Column(length: 255)]
    private string $password;

    /**
     * @var string[]
     */
    #[ORM\Column(type: 'json')]
    private array $roles;

    #[ORM\Column(type: 'datetimetz_immutable')]
    public readonly \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetimetz_immutable')]
    public readonly \DateTimeImmutable $updatedAt;

    public function __construct(
        #[ORM\Embedded(class: Email::class, columnPrefix: false)]
        public readonly Email $email,
        #[ORM\Column(length: 255)]
        public readonly string $firstName,
        #[ORM\Column(length: 255)]
        public readonly string $lastName,
    ) {
        $this->uuid = Uuid::uuid4();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $this->roles = [UserRole::ROLE_USER->value];
    }

    public function grantPrivilege(UserRole $role): void
    {
        if (\in_array($role->value, $this->roles)) {
            return;
        }

        $this->roles[] = $role->value;
    }

    public function hasPrivilege(UserRole $role): bool
    {
        return \in_array($role->value, $this->roles);
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string hashed version of password
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void
    {
        $this->password = '';
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    public function getFullName(): string
    {
        return \sprintf(
            '%s %s',
            $this->firstName,
            $this->lastName
        );
    }
}
