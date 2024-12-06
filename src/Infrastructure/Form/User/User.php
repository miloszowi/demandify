<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Form\User;

class User
{
    public function __construct(
        public string $email = '',
        public string $password = '',
        public string $firstName = '',
        public string $lastName = '',
    ) {}
}
