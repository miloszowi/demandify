<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Repository\ExternalService\Exception;

class InvalidExternalServiceConfiguration extends \Exception
{
    public static function fromValue(string $value): self
    {
        return new self(
            \sprintf('Invalid external service configuration found, expected to be valid json, found: %s', $value)
        );
    }
}
