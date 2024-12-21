<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Task\Adapter;

readonly class AdapterResult
{
    public function __construct(
        /** @var string[] */
        public array $columnNames,
        public int $rowCount,
        public int $executionTime,
        /** @var array<string[]> */
        public array $data,
    ) {}
}
