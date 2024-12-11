<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Form\Demand;

class Demand
{
    public function __construct(
        public ?string $service = null,
        public ?string $content = null,
        public ?string $reason = null,
    ) {}
}
