<?php

declare(strict_types=1);

namespace Demandify\Domain\ExternalService;

interface ExternalServiceRepository
{
    public function getByName(string $serviceName): ExternalService;

    /**
     * @return ExternalService[]
     */
    public function getAll(): array;
}
