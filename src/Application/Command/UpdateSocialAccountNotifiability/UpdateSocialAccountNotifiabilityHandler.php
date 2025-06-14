<?php

declare(strict_types=1);

namespace Demandify\Application\Command\UpdateSocialAccountNotifiability;

use Demandify\Application\Command\CommandHandler;
use Demandify\Domain\User\UserRepository;

class UpdateSocialAccountNotifiabilityHandler implements CommandHandler
{
    public function __construct(private readonly UserRepository $userRepository) {}

    public function __invoke(UpdateSocialAccountNotifiability $command): void
    {
        $user = $this->userRepository->getByUuid($command->userUuid);
        $user
            ->getSocialAccount($command->socialAccountType)
            ?->setNotifiable($command->notifiable)
        ;

        $this->userRepository->save($user);
    }
}
