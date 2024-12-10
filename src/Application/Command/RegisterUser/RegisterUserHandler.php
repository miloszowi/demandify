<?php

declare(strict_types=1);

namespace Querify\Application\Command\RegisterUser;

use Querify\Domain\DomainEventPublisher;
use Querify\Domain\User\Email;
use Querify\Domain\User\Event\UserRegistered;
use Querify\Domain\User\Exception\UserAlreadyRegisteredException;
use Querify\Domain\User\User;
use Querify\Domain\User\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RegisterUserHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly DomainEventPublisher $domainEventPublisher,
    ) {}

    public function __invoke(RegisterUser $command): void
    {
        $email = Email::fromString($command->email);

        if (null !== $this->userRepository->findByEmail($email)) {
            throw UserAlreadyRegisteredException::withEmail($command->email);
        }

        $user = new User($email, $command->name);

        foreach ($command->roles as $role) {
            $user->grantPrivilege($role);
        }

        $this->userRepository->save($user);
        $this->domainEventPublisher->publish(
            new UserRegistered($user->uuid)
        );
    }
}
