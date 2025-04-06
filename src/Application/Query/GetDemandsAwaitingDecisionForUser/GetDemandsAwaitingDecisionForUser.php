<?php

declare(strict_types=1);

namespace Demandify\Application\Query\GetDemandsAwaitingDecisionForUser;

use Demandify\Application\Query\Query;
use Ramsey\Uuid\UuidInterface;

readonly class GetDemandsAwaitingDecisionForUser implements Query
{
    public function __construct(public UuidInterface $userUuid) {}
}
