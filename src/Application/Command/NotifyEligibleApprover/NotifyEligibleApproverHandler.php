<?php

declare(strict_types=1);

namespace Querify\Application\Command\NotifyEligibleApprover;

use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\User\UserRepository;
use Querify\Domain\Notification\NotificationService;
use Querify\Domain\UserSocialAccount\UserSocialAccount;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class NotifyEligibleApproverHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly DemandRepository $demandRepository,
        private readonly NotificationService $notificationService
    ) {}

    public function __invoke(NotifyEligibleApprover $command): void
    {
        $user = $this->userRepository->getByUuid($command->eligibleApprover);
        $demand = $this->demandRepository->getByUuid($command->demand);

        /** @var UserSocialAccount $socialAccount */
        foreach ($user->getSocialAccounts() as $socialAccount) {
            // todo: maybe some bool on user social account to determine if should communicate through this channel?
            $this->notificationService->notifyAboutNewDemand($socialAccount, $demand);
        }
    }
}
