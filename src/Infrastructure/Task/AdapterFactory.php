<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Task;

use Querify\Domain\ExternalService\ExternalServiceType;

class AdapterFactory
{
    public function __construct(
        private readonly Adapter\Postgres $postgres,
        private readonly Adapter\Maria $maria
    ) {}

    public function create(ExternalServiceType $externalServiceType): Adapter
    {
        return match ($externalServiceType) {
            ExternalServiceType::POSTGRES => $this->postgres,
            ExternalServiceType::MARIADB => $this->maria,
        };
    }
}
