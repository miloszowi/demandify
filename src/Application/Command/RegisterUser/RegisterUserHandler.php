<?php

declare(strict_types=1);

namespace Demandify\Application\Command\RegisterUser;

use Demandify\Domain\DomainEventPublisher;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\Event\UserRegistered;
use Demandify\Domain\User\Exception\UserAlreadyRegisteredException;
use Demandify\Domain\User\User;
use Demandify\Domain\User\UserRepository;
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
