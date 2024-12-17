<?php

declare(strict_types=1);

namespace Querify\Domain\Demand\Exception;

use Querify\Domain\Exception\DomainLogicException;
use Querify\Domain\User\User;

class UserNotAuthorizedToUpdateDemandException extends DomainLogicException
{
    public static function fromUser(User $user, string $service): self
    {
        return new self(
            \sprintf('User "%s" is not privileged to accept/decline demand for service "%s".', $user->email, $service)
        );
    }
}
