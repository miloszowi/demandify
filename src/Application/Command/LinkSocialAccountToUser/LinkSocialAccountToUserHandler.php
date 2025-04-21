<?php

declare(strict_types=1);

namespace Demandify\Application\Command\LinkSocialAccountToUser;

use Demandify\Application\Command\CommandHandler;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\UserRepository;
use Demandify\Domain\UserSocialAccount\Exception\UserSocialAccountAlreadyLinkedException;
use Demandify\Domain\UserSocialAccount\UserSocialAccount;

class LinkSocialAccountToUserHandler implements CommandHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    public function __invoke(LinkSocialAccountToUser $command): void
    {
        $user = $this->userRepository->getByEmail(Email::fromString($command->userEmail));

        if (null !== $user->getSocialAccount($command->type)) {
            throw UserSocialAccountAlreadyLinkedException::fromPair($user->uuid, $command->type);
        }

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
