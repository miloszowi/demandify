<?php

declare(strict_types=1);

namespace Demandify\Domain\Demand\Exception;

use Demandify\Domain\Demand\Status;
use Demandify\Domain\DomainLogicException;

class InvalidDemandStatusException extends DomainLogicException
{
    public static function forApproving(Status $status): self
    {
        return new self(
            \sprintf(
                'Can not approve demand with status "%s", only "%s" status is allowed.',
                $status->value,
                Status::NEW->value
            )
        );
    }

    public static function forDeclining(Status $status): self
    {
        return new self(
            \sprintf(
                'Can not decline demand with status "%s", only "%s" status is allowed.',
                $status->value,
                Status::NEW->value
            )
        );
    }
}
