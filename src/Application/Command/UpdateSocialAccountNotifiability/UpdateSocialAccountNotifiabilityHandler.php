<?php

declare(strict_types=1);

namespace Demandify\Application\Command\UpdateSocialAccountNotifiability;

use Demandify\Application\Command\CommandHandler;
use Demandify\Domain\UserSocialAccount\UserSocialAccountRepository;

class UpdateSocialAccountNotifiabilityHandler implements CommandHandler
{
    public function __construct(private readonly UserSocialAccountRepository $userSocialAccountRepository) {}

    public function __invoke(UpdateSocialAccountNotifiability $command): void
    {
        $socialAccount = $this->userSocialAccountRepository->getByUserUuidAndType(
            $command->userUuid,
            $command->socialAccountType
        );

        $socialAccount->setNotifiable($command->notifiable);

        $this->userSocialAccountRepository->save($socialAccount);
    }
}
