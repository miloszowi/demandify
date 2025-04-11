<?php

declare(strict_types=1);

namespace Demandify\Application\Query\GetDemandsToBeReviewedForUser;

use Demandify\Application\Query\Query;
use Ramsey\Uuid\UuidInterface;

readonly class GetDemandsToBeReviewedForUser implements Query
{
    public function __construct(public UuidInterface $userUuid) {}
}
