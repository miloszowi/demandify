<?php

declare(strict_types=1);

namespace Querify\Application\Query\GetUser;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
final readonly class GetUserByEmail
{
    public function __construct(public string $email) {}
}
