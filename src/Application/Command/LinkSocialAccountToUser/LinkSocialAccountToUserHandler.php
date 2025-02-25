<?php

declare(strict_types=1);

namespace Demandify\Application\Command\LinkSocialAccountToUser;

use Demandify\Application\Command\RegisterUser\RegisterUser;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\Exception\UserNotFoundException;
use Demandify\Domain\User\UserRepository;
use Demandify\Domain\User\UserRole;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;
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

            $this->userRepository->save($user);
        }
    }
}
