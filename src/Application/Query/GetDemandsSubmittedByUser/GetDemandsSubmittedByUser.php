<?php

declare(strict_types=1);

namespace Demandify\Application\Query\GetDemandsSubmittedByUser;

use Demandify\Application\Query\Query;
use Ramsey\Uuid\UuidInterface;

readonly class GetDemandsSubmittedByUser implements Query
{
    public function __construct(
        public UuidInterface $userUuid,
        public int $page,
        public int $limit,
        public ?string $search = null
    ) {}
}
