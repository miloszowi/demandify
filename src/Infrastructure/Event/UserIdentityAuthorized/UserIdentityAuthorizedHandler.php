<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Event\UserIdentityAuthorized;

use Demandify\Application\Command\LinkSocialAccountToUser\LinkSocialAccountToUser;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class UserIdentityAuthorizedHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MessageBusInterface $messageBus
    ) {}

    public function __invoke(UserIdentityAuthorized $event): void
    {
        $user = $this->userRepository->getByEmail(Email::fromString($event->email));

        if ($user->hasSocialAccountLinked($event->type)) {
            return;
        }

        $this->messageBus->dispatch(
            new LinkSocialAccountToUser(
                $event->email,
                $event->type,
                $event->externalUserId,
                $event->extraData
            )
        );
    }
}
