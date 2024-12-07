<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Api\Authentication;

use Querify\Domain\User\Email;
use Querify\Domain\User\Exception\UserNotFoundException;
use Querify\Domain\User\User;
use Querify\Domain\User\UserRepository;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    public function __construct(private readonly UserRepository $userRepository) {}

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
        try {
            return $this->userRepository->getByEmail(Email::fromString($identifier));
        } catch (UserNotFoundException) {
            throw new UnauthorizedHttpException('Bearer', message: 'Invalid credentials');
        } catch (\InvalidArgumentException) {
            throw new BadRequestException('Username is not a valid email.');
        }
    }
}
