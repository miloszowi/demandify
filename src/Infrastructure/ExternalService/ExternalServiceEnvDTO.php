<?php

declare(strict_types=1);

namespace Querify\Infrastructure\ExternalService;

final readonly class ExternalServiceEnvDTO
{
    public function __construct(
        public string $type,
        public string $name,
        public string $host,
        public string $user,
        public string $password,
        public int $port
    ) {}
}
