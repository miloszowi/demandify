<?php

declare(strict_types=1);

namespace Querify\Domain\ExternalService;

use Querify\Domain\ExternalService\Exception\ExternalServiceConfigurationNotFoundException;

interface ExternalServiceConfigurationRepository
{
    public function save(ExternalServiceConfiguration $configuration): void;

    public function findByName(string $name): ?ExternalServiceConfiguration;

    /**
     * @throws ExternalServiceConfigurationNotFoundException
     */
    public function getByName(string $name): ?ExternalServiceConfiguration;
}
