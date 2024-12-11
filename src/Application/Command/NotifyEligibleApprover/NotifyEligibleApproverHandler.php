<?php

declare(strict_types=1);

namespace Querify\Application\Command\NotifyEligibleApprover;

use Querify\Domain\User\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class NotifyEligibleApproverHandler
{
    public function __construct(private readonly UserRepository $userRepository) {}

    public function __invoke(NotifyEligibleApprover $command): void
    {
        dd($this->userRepository);
        //        $user = $this->userRepository->getByUuid($command->eligibleApproverUuid);
        //        foreach ($user->getSocialAccounts() as $socialAccount) {
        //            $socialAccount->
        //        }
    }
}
