<?php

declare(strict_types=1);

namespace Demandify\Domain\Demand\Exception;

use Demandify\Domain\DomainLogicException;
use Demandify\Domain\User\User;

class UserNotAuthorizedToUpdateDemandException extends DomainLogicException
{
    public static function fromUser(User $user, string $service): self
    {
        return new self(
            \sprintf('User "%s" is not privileged to accept/decline demand for service "%s".', $user->email, $service)
        );
    }
}
