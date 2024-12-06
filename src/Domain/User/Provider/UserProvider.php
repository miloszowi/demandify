<?php

declare(strict_types=1);

namespace Querify\Domain\User\Provider;

use Querify\Domain\User\Email;
use Querify\Domain\User\User;
use Querify\Domain\User\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

readonly class UserProvider implements UserProviderInterface
{
    public function __construct(private UserRepository $userRepository) {}

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->userRepository->getByEmail(Email::fromString($identifier));
    }
}
