<?php

declare(strict_types=1);

namespace Demandify\Domain\ExternalService;

use Demandify\Domain\ExternalService\Exception\ExternalServiceNotFoundException;

interface ExternalServiceRepository
{
    /**
     * @throws ExternalServiceNotFoundException
     */
    public function getByName(string $serviceName): ExternalService;

    /**
     * @return ExternalService[]
     */
    public function getAll(): array;
}
