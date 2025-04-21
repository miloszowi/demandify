<?php

declare(strict_types=1);

namespace Demandify\Application\Command\SubmitDemand;

use Demandify\Application\Command\CommandHandler;
use Demandify\Domain\Demand\Demand;
use Demandify\Domain\Demand\DemandRepository;
use Demandify\Domain\User\Email;
use Demandify\Domain\User\UserRepository;

final class SubmitDemandHandler implements CommandHandler
{
    public function __construct(
        private readonly DemandRepository $demandRepository,
        private readonly UserRepository $userRepository,
    ) {}

    public function __invoke(SubmitDemand $command): void
    {
        $user = $this->userRepository->getByEmail(Email::fromString($command->requesterEmail));

        $demand = new Demand(
            $user,
            $command->service,
            $command->content,
            $command->reason
        );

        $this->demandRepository->save($demand);
    }
}
