<?php

declare(strict_types=1);

namespace Demandify\Application\Event\UserRegistered;

use Demandify\Application\Event\DomainEventHandler;
use Demandify\Domain\User\Event\UserRegistered;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UserRegisteredHandler implements DomainEventHandler
{
    public function __invoke(UserRegistered $event): void
    {
        // TODO: Notify
    }
}
