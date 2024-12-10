<?php

declare(strict_types=1);

namespace Querify\Application\Command\RegisterUser;

use Querify\Domain\User\UserRole;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
final readonly class RegisterUser
{
    public function __construct(
        public string $email,
        public string $name,
        /**
         * @var UserRole[]
         */
        public array $roles,
    ) {}
}
