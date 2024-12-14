<?php

declare(strict_types=1);

namespace Querify\Application\Command\ApproveDemand;

use Querify\Domain\Demand\DemandRepository;
use Querify\Domain\Demand\Event\DemandApproved;
use Querify\Domain\DomainEventPublisher;
use Querify\Domain\UserSocialAccount\UserSocialAccountRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ApproveDemandHandler
{
    public function __construct(
        private readonly UserSocialAccountRepository $userSocialAccountRepository,
        private readonly DemandRepository $demandRepository,
        private readonly DomainEventPublisher $domainEventPublisher,
    ) {}

    public function __invoke(ApproveDemand $command): void
    {
        $demand = $this->demandRepository->getByUuid($command->demandUuid);
        $userSocialAccount = $this->userSocialAccountRepository->getByExternalIdAndType($command->externalUserId, $command->userSocialAccountType);

        $demand->approveBy($userSocialAccount->user);
        $this->demandRepository->save($demand);
        $this->domainEventPublisher->publish(
            new DemandApproved($demand->uuid)
        );
    }
}
