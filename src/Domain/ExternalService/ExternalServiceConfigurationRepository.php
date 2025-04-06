<?php

declare(strict_types=1);

namespace Demandify\Domain\ExternalService;

use Demandify\Domain\ExternalService\Exception\ExternalServiceConfigurationNotFoundException;
use Ramsey\Uuid\UuidInterface;

interface ExternalServiceConfigurationRepository
{
    public function save(ExternalServiceConfiguration $configuration): void;

    public function findByName(string $name): ?ExternalServiceConfiguration;

    /**
     * @throws ExternalServiceConfigurationNotFoundException
     */
    public function getByName(string $name): ?ExternalServiceConfiguration;

    /**
     * @return ExternalServiceConfiguration[]
     */
    public function findEligibleForUser(UuidInterface $userUuid): array;
}
