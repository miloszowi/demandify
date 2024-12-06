<?php

declare(strict_types=1);

namespace Querify\Domain\Demand\Exception;

use Ramsey\Uuid\UuidInterface;

class DemandNotFoundException extends \Exception
{
    public static function fromUuid(UuidInterface $uuid): self
    {
        return new self(
            \sprintf('Demand with uuid of "%s" was not found.', $uuid->toString())
        );
    }
}
