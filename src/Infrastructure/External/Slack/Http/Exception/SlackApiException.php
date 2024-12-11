<?php

declare(strict_types=1);

namespace Querify\Infrastructure\External\Slack\Http\Exception;

class SlackApiException extends \Exception
{
    public static function fromError(string $error): self
    {
        return new self(
            \sprintf('Slack API responded with an error: %s', $error)
        );
    }
}
