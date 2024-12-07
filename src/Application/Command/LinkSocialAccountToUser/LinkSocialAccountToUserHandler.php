<?php

declare(strict_types=1);

namespace Querify\Application\Command\LinkSocialAccountToUser;

use Querify\Domain\User\Email;
use Querify\Domain\User\UserRepository;
use Querify\Domain\UserSocialAccount\Exception\UserSocialAccountAlreadyLinkedException;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Querify\Domain\UserSocialAccount\UserSocialAccountRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LinkSocialAccountToUserHandler
{
    public function __construct(
        private readonly UserSocialAccountRepository $userSocialAccountRepository,
        private readonly UserRepository $userRepository
    ) {}

    public function __invoke(LinkSocialAccountToUser $command): void
    {
        $user = $this->userRepository->getByEmail(Email::fromString($command->userEmail));

        if (null !== $this->userSocialAccountRepository->findByPair($user->uuid, $command->type)) {
            throw UserSocialAccountAlreadyLinkedException::fromPair($user->uuid, $command->type);
        }

        $this->userSocialAccountRepository->save(
            new UserSocialAccount(
                $user->uuid,
                $command->type,
                $command->externalId,
                $command->extraData
            )
        );
    }
}
