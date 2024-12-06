<?php

declare(strict_types=1);

namespace Querify\Application\Event\UserRegistered;

use Querify\Domain\User\Event\UserRegistered;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UserRegisteredHandler
{
    public function __invoke(UserRegistered $event): void
    {
        // TODO: Notify
    }
}
