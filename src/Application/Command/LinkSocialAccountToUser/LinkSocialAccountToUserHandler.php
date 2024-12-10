<?php

declare(strict_types=1);

namespace Querify\Application\Command\LinkSocialAccountToUser;

use Querify\Application\Command\RegisterUser\RegisterUser;
use Querify\Domain\User\Email;
use Querify\Domain\User\Exception\UserNotFoundException;
use Querify\Domain\User\UserRepository;
use Querify\Domain\User\UserRole;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class LinkSocialAccountToUserHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MessageBusInterface $messageBus,
    ) {}

    public function __invoke(LinkSocialAccountToUser $command): void
    {
        try {
            $user = $this->userRepository->getByEmail(Email::fromString($command->userEmail));
        } catch (UserNotFoundException) {
            $this->messageBus->dispatch(
                new RegisterUser(
                    $command->userEmail,
                    $command->name,
                    [UserRole::ROLE_USER],
                )
            );

            $user = $this->userRepository->getByEmail(Email::fromString($command->userEmail));
        }

        if (null === $user->getSocialAccount($command->type)) {
            $user->linkSocialAccount(
                new UserSocialAccount(
                    $user,
                    $command->type,
                    $command->externalId,
                    $command->extraData
                )
            );
        }

        $this->userRepository->save($user);
    }
}
