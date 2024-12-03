<?php

declare(strict_types=1);

namespace Querify\Application\Command\RegisterUser;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
final readonly class RegisterUser
{
    public function __construct(
        public string $email,
        #[\SensitiveParameter]
        public string $plainPassword,
        public string $firstName,
        public string $lastName,
        public array $roles,
    ) {
    }
}