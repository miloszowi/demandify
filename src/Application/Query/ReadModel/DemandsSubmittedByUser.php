<?php

declare(strict_types=1);

namespace Demandify\Application\Query\ReadModel;

readonly class DemandsSubmittedByUser
{
    public function __construct(
        /** @var mixed[] */ // todo: fix this
        public array $demands,
        public int $total,
        public int $page,
        public int $limit,
        public int $totalPages,
        public ?string $search
    ) {}
}
