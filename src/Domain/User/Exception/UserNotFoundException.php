<?php

declare(strict_types=1);

namespace Demandify\Domain\User\Exception;

use Demandify\Domain\User\Email;
use Ramsey\Uuid\UuidInterface;

class UserNotFoundException extends \Exception
{
    public static function fromUuid(UuidInterface $uuid): self
    {
        return new self(
            \sprintf('User with uuid of "%s" was not found.', $uuid->toString())
        );
    }

    public static function fromEmail(Email $email): self
    {
        return new self(
            \sprintf('User with email of "%s" was not found.', (string) $email)
        );
    }
}
