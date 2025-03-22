<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\External\Google\Http\Exception;

class GoogleApiException extends \Exception
{
    public static function fromError(string $error): self
    {
        return new self(
            \sprintf('Google API responded with an error: %s', $error)
        );
    }
}
