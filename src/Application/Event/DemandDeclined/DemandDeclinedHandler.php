<?php

declare(strict_types=1);

namespace Querify\Application\Event\DemandDeclined;

use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\Demand\Event\DemandDeclined;
use Querify\Domain\Notification\NotificationService;
use Querify\Domain\User\UserRepository;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DemandDeclinedHandler
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly DemandRepository $demandRepository,
        private readonly UserRepository $userRepository
    ) {}

    public function __invoke(DemandDeclined $event): void
    {
        $demand = $this->demandRepository->getByUuid($event->demandUuid);
        $user = $this->userRepository->getByUuid($demand->requesterUuid);

        /** @var UserSocialAccount $socialAccount */
        foreach ($user->getSocialAccounts() as $socialAccount) {
            // todo: maybe some bool on user social account to determine if should communicate through this channel?
            $this->notificationService->notifyDemandDecisionMade(
                $socialAccount,
                $demand
            );
        }
    }
}
