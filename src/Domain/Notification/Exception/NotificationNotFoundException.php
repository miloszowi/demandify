<?php

declare(strict_types=1);

namespace Querify\Domain\Notification\Exception;

use Ramsey\Uuid\UuidInterface;

class NotificationNotFoundException extends \Exception
{
    public static function fromDemandUuid(UuidInterface $demandUuid): self
    {
        return new self(
            \sprintf('Notification for demand with uuid of "%s" was not found.', $demandUuid->toString())
        );
    }
}
