<?php

declare(strict_types=1);

namespace Querify\Application\Command\LinkSocialAccountToUser;

use Querify\Domain\User\Email;
use Querify\Domain\User\UserRepository;
use Querify\Domain\UserSocialAccount\Exception\UserSocialAccountAlreadyLinkedException;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LinkSocialAccountToUserHandler
{
    public function __construct(private readonly UserRepository $userRepository) {}

    public function __invoke(LinkSocialAccountToUser $command): void
    {
        $user = $this->userRepository->getByEmail(Email::fromString($command->userEmail));

        if (null !== $user->getSocialAccount($command->type)) {
            throw UserSocialAccountAlreadyLinkedException::fromPair($user->uuid, $command->type);
        }

        $user->addSocialAccount(
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
