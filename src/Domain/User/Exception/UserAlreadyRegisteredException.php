<?php

declare(strict_types=1);

namespace Querify\Domain\User\Exception;

class UserAlreadyRegisteredException extends \Exception
{
    public static function withEmail(string $email): self
    {
        return new self(
            \sprintf('User with email "%s" is already registered.', $email)
        );
    }
}