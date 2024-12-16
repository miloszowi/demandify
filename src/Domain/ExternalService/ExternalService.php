<?php

declare(strict_types=1);

namespace Querify\Domain\ExternalService;

readonly class ExternalService
{
    public function __construct(
        public ExternalServiceType $type,
        public string $name,
        #[\SensitiveParameter]
        public string $serviceName,
        #[\SensitiveParameter]
        public string $host,
        #[\SensitiveParameter]
        public string $user,
        #[\SensitiveParameter]
        public string $password,
        #[\SensitiveParameter]
        public int $port,
    ) {}
}
