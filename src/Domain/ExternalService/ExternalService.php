<?php

declare(strict_types=1);

namespace Querify\Domain\ExternalService;

readonly class ExternalService
{
    public function __construct(
        public ExternalServiceType $type,
        public string $name,
        public string $serviceName,
        public string $host,
        public string $user,
        public string $password,
        public int $port,
    ) {}
}
