<?php

declare(strict_types=1);

namespace Querify\Application\Command\NotifyEligibleApprover;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class NotifyEligibleApproverHandler
{
    public function __invoke(NotifyEligibleApprover $command): void
    {
        // TODO
    }
}
