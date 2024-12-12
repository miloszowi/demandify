<?php

declare(strict_types=1);

namespace Querify\Domain\ExternalService;

interface ExternalServiceConfigurationRepository
{
    public function save(ExternalServiceConfiguration $configuration): void;

    public function findByName(string $name): ?ExternalServiceConfiguration;
}
