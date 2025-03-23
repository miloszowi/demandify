<?php

declare(strict_types=1);

namespace Demandify\Application\Query\GetUser;

use Demandify\Application\Query\Query;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage]
final readonly class GetUserByEmail implements Query
{
    public function __construct(public string $email) {}
}
