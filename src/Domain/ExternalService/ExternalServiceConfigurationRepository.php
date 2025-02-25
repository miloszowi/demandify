<?php

declare(strict_types=1);

namespace Demandify\Domain\ExternalService;

use Demandify\Domain\ExternalService\Exception\ExternalServiceConfigurationNotFoundException;

interface ExternalServiceConfigurationRepository
{
    public function save(ExternalServiceConfiguration $configuration): void;

    public function findByName(string $name): ?ExternalServiceConfiguration;

    /**
     * @throws ExternalServiceConfigurationNotFoundException
     */
    public function getByName(string $name): ?ExternalServiceConfiguration;
}
