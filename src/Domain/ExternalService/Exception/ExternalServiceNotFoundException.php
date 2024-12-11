<?php

declare(strict_types=1);

namespace Querify\Domain\ExternalService\Exception;

class ExternalServiceNotFoundException extends \Exception
{
    public static function fromName(string $name): self
    {
        return new self(
            \sprintf('External service with name "%s" was not found', $name)
        );
    }
}
