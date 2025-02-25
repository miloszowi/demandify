<?php

declare(strict_types=1);

namespace Demandify\Domain\User\Provider;

use Demandify\Domain\User\Email;
use Demandify\Domain\User\User;
use Demandify\Domain\User\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    public function __construct(private UserRepository $userRepository) {}

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->userRepository->getByEmail(Email::fromString($user->getUserIdentifier()));
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
