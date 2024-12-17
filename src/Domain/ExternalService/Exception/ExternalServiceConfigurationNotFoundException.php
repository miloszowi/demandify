<?php

declare(strict_types=1);

namespace Querify\Domain\ExternalService\Exception;

class ExternalServiceConfigurationNotFoundException extends \Exception
{
    public static function fromName(string $name): self
    {
        return new self(
            \sprintf('External service "%s" configuration was not found', $name)
        );
    }
}
