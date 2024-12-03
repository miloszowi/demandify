<?php

declare(strict_types=1);

namespace Querify\Application\Command\RegisterUser;

use Querify\Application\Event\UserRegistered\UserRegistered;
use Querify\Domain\User\Email;
use Querify\Domain\User\Exception\UserAlreadyRegisteredException;
use Querify\Domain\User\User;
use Querify\Domain\User\UserRepository;
use Querify\Domain\UserRole;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
class RegisterUserHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    public function __invoke(RegisterUser $command): void
    {
        $email = Email::fromString($command->email);

        if (null !== $this->userRepository->findByEmail($email)) {
            throw UserAlreadyRegisteredException::withEmail($command->email);
        }

        $user = new User($email, $command->firstName, $command->lastName);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $command->plainPassword)
        );

        foreach ($command->roles as $role) {
            $user->grantPrivilege(UserRole::from($role));
        }

        $this->userRepository->save($user);
        $this->messageBus->dispatch(
            new UserRegistered($user->uuid)
        );
    }
}